import sys
import argparse
from playwright.sync_api import sync_playwright
from scrapers.wb import WBScraper
from scrapers.avito import AvitoScraper
from utils.csv_exporter import export_to_csv

def main():
    parser = argparse.ArgumentParser(description="Marketplace Scraper")
    parser.add_argument("--wb-seller", default="https://www.wildberries.ru/seller/4203897", help="Wildberries seller URL")
    parser.add_argument("--avito-seller", default="https://www.avito.ru/brands/i2104250/items/all?s=profile_search_show_all&sellerId=7bae82bce3a848fef773b1099a5e17de", help="Avito seller URL")
    parser.add_argument("--headless", action="store_true", help="Run in headless mode")
    parser.add_argument("--output", default="products.csv", help="Output CSV file")

    args = parser.parse_args()

    all_products = []

    with sync_playwright() as p:
        # Launch browser
        # For avoiding detection, we should use a realistic user agent
        # and maybe non-headless if run locally.
        browser = p.chromium.launch(headless=args.headless)
        context = browser.new_context(
            user_agent="Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36"
        )

        # 1. Scrape Avito
        if args.avito_seller:
            try:
                print("--- Starting Avito Scraping ---")
                page = context.new_page()
                avito = AvitoScraper(page)

                # Get item URLs
                item_urls = avito.get_seller_items(args.avito_seller)
                print(f"Found {len(item_urls)} Avito items.")

                # Parse each
                for url in item_urls:
                    try:
                        prod = avito.parse_product(url)
                        all_products.append(prod)
                        print(f"Parsed: {prod.name} ({prod.price} RUB)")
                    except Exception as e:
                        print(f"Failed to parse {url}: {e}")

                page.close()
            except Exception as e:
                print(f"Avito scraping failed: {e}")

        # 2. Scrape Wildberries
        if args.wb_seller:
            try:
                print("--- Starting WB Scraping ---")
                page = context.new_page()
                wb = WBScraper(page)

                item_urls = wb.get_seller_items(args.wb_seller)
                print(f"Found {len(item_urls)} WB items.")

                for url in item_urls:
                    try:
                        prod = wb.parse_product(url)
                        all_products.append(prod)
                        print(f"Parsed: {prod.name} ({prod.price} RUB)")
                    except Exception as e:
                        print(f"Failed to parse {url}: {e}")

                page.close()
            except Exception as e:
                print(f"WB scraping failed: {e}")

        browser.close()

    # Export
    if all_products:
        export_to_csv(all_products, args.output)
    else:
        print("No products found.")

if __name__ == "__main__":
    main()
