import time
import re
from playwright.sync_api import Page
from models.product import Product

class WBScraper:
    def __init__(self, page: Page):
        self.page = page
        self.base_url = "https://www.wildberries.ru"

    def get_seller_items(self, seller_url: str) -> list[str]:
        print(f"Navigating to WB seller: {seller_url}")
        self.page.goto(seller_url, timeout=60000)

        # WB is infinite scroll.
        # We need to scroll down until no new items appear.

        item_urls = set()
        previous_count = 0
        attempts = 0

        while attempts < 20: # Limit scrolls
            # Collect items
            # WB selectors change often. Currently usually .product-card__link
            cards = self.page.locator("article.product-card, div.product-card")

            # Extract links
            links = self.page.locator("a.product-card__link, a.product-card__main")
            count = links.count()

            print(f"Found {count} items so far...")

            for i in range(count):
                href = links.nth(i).get_attribute("href")
                if href:
                    if not href.startswith("http"):
                        href = self.base_url + href
                    item_urls.add(href)

            if count == previous_count:
                # No new items, maybe end of list or need to wait
                time.sleep(2)
                # Try scrolling again

            previous_count = count

            # Scroll down
            self.page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
            time.sleep(2)

            # Check if we are at bottom or loader exists
            # WB often lazy loads.

            # Heuristic break: if we haven't found new items in 3 loops, break
            if attempts > 5 and count == previous_count:
                break

            attempts += 1

        return list(item_urls)

    def parse_product(self, url: str) -> Product:
        print(f"Parsing WB product: {url}")
        self.page.goto(url, timeout=60000)

        # Init product
        product = Product(
            id="", name="", description="", price=0.0, url=url, source="Wildberries"
        )

        # ID from URL
        # /catalog/12345/detail.aspx
        try:
            match = re.search(r'catalog/(\d+)/detail', url)
            if match:
                product.id = "WB-" + match.group(1)
        except:
            pass

        # Wait for title
        try:
            self.page.wait_for_selector("h1", timeout=10000)
            product.name = self.page.locator("h1").inner_text().strip()
        except:
            pass

        # Price
        try:
            # price-block__final-price
            price_el = self.page.locator(".price-block__final-price").first
            if price_el.is_visible():
                price_text = price_el.inner_text()
                price_clean = re.sub(r'[^\d]', '', price_text)
                product.price = float(price_clean)

            # Original price
            old_price_el = self.page.locator(".price-block__old-price").first
            if old_price_el.is_visible():
                price_text = old_price_el.inner_text()
                price_clean = re.sub(r'[^\d]', '', price_text)
                product.original_price = float(price_clean)
        except:
            pass

        # Description
        try:
            # Click "Expand description" if needed
            # .collapsible__toggle
            toggle = self.page.locator(".collapsible__toggle").filter(has_text="Развернуть описание")
            if toggle.is_visible():
                toggle.click()

            desc_el = self.page.locator(".product-page__description p, .j-description p")
            if desc_el.count() > 0:
                product.description = "\n".join([desc_el.nth(i).inner_text() for i in range(desc_el.count())])
        except:
            pass

        # Images
        try:
            # Usually a slider or thumbnails
            # .slide__content img
            imgs = self.page.locator(".slide__content img")
            count = imgs.count()
            seen = set()
            for i in range(count):
                src = imgs.nth(i).get_attribute("src")
                if src:
                    # Often src is //basket-10.wb.ru...
                    if src.startswith("//"):
                        src = "https:" + src

                    # Get high res
                    # tm/ -> big/
                    # usually .../images/tm/516.jpg -> .../images/big/516.jpg
                    src_high = src.replace("/tm/", "/big/")

                    if src_high not in seen:
                        product.images.append(src_high)
                        seen.add(src_high)
        except:
            pass

        # Specs / Params
        try:
            rows = self.page.locator("table.product-params__table tr")
            for i in range(rows.count()):
                row = rows.nth(i)
                th = row.locator("th").inner_text().strip().lower()
                td = row.locator("td").inner_text().strip()

                if "состав" in th or "материал" in th:
                    product.material = td
                elif "цвет" in th:
                    product.color = td
                elif "страна" in th:
                    product.country_of_origin = td
                elif "бренд" in th:
                    product.brand = td
                elif "артикул" in th:
                    product.vendor_code = td
                else:
                    product.extra_data[th] = td
        except:
            pass

        # Rating
        try:
            rating_el = self.page.locator(".product-review__rating")
            if rating_el.is_visible():
                product.rating = float(rating_el.inner_text().strip())
        except:
            pass

        return product
