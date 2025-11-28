import time
import re
from playwright.sync_api import Playwright, Page
from models.product import Product

class AvitoScraper:
    def __init__(self, page: Page):
        self.page = page
        self.base_url = "https://www.avito.ru"

    def get_seller_items(self, seller_url: str) -> list[str]:
        """
        Navigates to the seller's page and collects all item URLs.
        """
        print(f"Navigating to seller: {seller_url}")
        self.page.goto(seller_url, timeout=60000)

        # Wait for items to load
        try:
            self.page.wait_for_selector("div[data-marker='item']", timeout=10000)
        except:
            print("No items found or page blocked.")
            return []

        item_urls = set()

        while True:
            # Scroll to bottom to ensure all elements are rendered (if infinite scroll)
            # Avito usually uses pagination or 'Show more'.
            # We'll check for pagination.

            items = self.page.locator("div[data-marker='item']")
            count = items.count()
            print(f"Found {count} items on current view.")

            for i in range(count):
                item = items.nth(i)
                # Try to get link
                try:
                    link_el = item.locator("a[itemprop='url']").first
                    if not link_el.is_visible():
                        link_el = item.locator("a").first

                    href = link_el.get_attribute("href")
                    if href:
                        full_url = self.base_url + href
                        item_urls.add(full_url)
                except Exception as e:
                    print(f"Error extracting link: {e}")

            # Check for "Next page" or "Show more"
            # Selector for pagination next button usually contains 'pagination-next' or similar
            # Or data-marker="pagination-button/next"
            next_btn = self.page.locator("a[data-marker='pagination-button/next']")
            if next_btn.is_visible():
                print("Navigating to next page...")
                next_btn.click()
                self.page.wait_for_timeout(3000) # Wait for load
            else:
                break

        return list(item_urls)

    def parse_product(self, url: str) -> Product:
        print(f"Parsing product: {url}")
        self.page.goto(url, timeout=60000)
        self.page.wait_for_load_state("domcontentloaded")

        # Init product
        product = Product(
            id="", name="", description="", price=0.0, url=url, source="Avito"
        )

        # ID
        # usually in URL or item-id
        try:
            product.id = "AVITO-" + url.split("_")[-1]
            item_id_el = self.page.locator("span[data-marker='item-view/item-id']")
            if item_id_el.is_visible():
                product.id = "AVITO-" + item_id_el.inner_text().replace("№", "").strip()
        except:
            pass

        # Title
        try:
            title_el = self.page.locator("h1[data-marker='item-view/title-info']")
            if title_el.is_visible():
                product.name = title_el.inner_text().strip()
        except:
            pass

        # Price
        try:
            price_el = self.page.locator("span[data-marker='item-view/item-price']")
            if price_el.is_visible():
                price_text = price_el.get_attribute("content") # meta content usually holds cleaner number
                if not price_text:
                    price_text = price_el.inner_text()

                # Clean up "10 000 ₽" -> 10000
                price_clean = re.sub(r'[^\d]', '', price_text)
                product.price = float(price_clean)
        except:
            pass

        # Description
        try:
            desc_el = self.page.locator("div[data-marker='item-view/item-description']")
            if desc_el.is_visible():
                product.description = desc_el.inner_text().strip()
        except:
            pass

        # Images
        try:
            # Click on gallery to ensure full images loaded?
            # Usually looking at thumbnails or main image slider
            # data-marker="image-frame/image-wrapper"
            images_locs = self.page.locator("div[data-marker='image-frame/image-wrapper'] img")
            count = images_locs.count()
            if count == 0:
                 # Try finding thumbnails
                 images_locs = self.page.locator("li[data-marker='item-view-gallery-thumb'] img")
                 count = images_locs.count()

            seen_urls = set()
            for i in range(count):
                src = images_locs.nth(i).get_attribute("src")
                if src and src not in seen_urls:
                    # Often src is a small version, check if there's a higher res available
                    # Avito image URLs usually look like .../640x480/...
                    # We can try to replace with .../1280x960/... if pattern matches
                    # But keeping it simple for now.
                    product.images.append(src)
                    seen_urls.add(src)
        except:
            pass

        # Category / Breadcrumbs
        try:
            crumbs = self.page.locator("div[data-marker='breadcrumbs'] a span")
            crumb_texts = [crumbs.nth(i).inner_text() for i in range(crumbs.count())]
            if len(crumb_texts) > 0:
                product.category = crumb_texts[0]
            if len(crumb_texts) > 1:
                product.subcategory = crumb_texts[1]
            if len(crumb_texts) > 2:
                product.product_type = crumb_texts[-1]
        except:
            pass

        # Attributes / Params
        # Avito lists params in a ul
        try:
            params = self.page.locator("ul[data-marker='item-view/item-params'] li")
            for i in range(params.count()):
                text = params.nth(i).inner_text()
                # Text is usually "Condition: New"
                if ":" in text:
                    key, val = text.split(":", 1)
                    key = key.strip().lower()
                    val = val.strip()

                    if "состояние" in key:
                        product.extra_data["Состояние"] = val
                    elif "производитель" in key or "бренд" in key:
                        product.brand = val
                    elif "цвет" in key:
                        product.color = val
                    elif "тип" in key:
                        # might override product_type
                        pass
                    else:
                        product.extra_data[key] = val
        except:
            pass

        # Views & Date
        try:
            stats_text = self.page.locator("span[data-marker='item-view/item-date']").inner_text()
            product.date_added = stats_text # Contains date
        except:
            pass

        return product
