# Marketplace Scraper

This project scrapes products from Wildberries and Avito sellers and exports them to a CSV file suitable for WooCommerce import.

## Requirements

- Python 3.8+
- Playwright
- Pandas

## Installation

1. Create a virtual environment:
   ```bash
   python -m venv venv
   source venv/bin/activate  # On Windows: venv\Scripts\activate
   ```

2. Install dependencies:
   ```bash
   pip install -r requirements.txt
   ```

3. Install Playwright browsers:
   ```bash
   playwright install chromium
   ```

## Usage

Run the main script:

```bash
python main.py
```

Arguments:
- `--wb-seller`: URL of the Wildberries seller page.
- `--avito-seller`: URL of the Avito seller page.
- `--headless`: Run browser in headless mode (default: False).
- `--output`: Output CSV filename (default: `products.csv`).

## Anti-Bot Protection

Both Wildberries and Avito have strong anti-bot protections.
- **Avito**: Often blocks IP ranges (especially cloud/VPN). Use a residential IP or run locally.
- **Wildberries**: May require solving CAPTCHA or handling infinite scroll carefully.

If you are blocked, try:
1. Running locally on your machine.
2. Using proxies.
3. Using `playwright-stealth` (install via pip and integrate).

## Output

The script generates `products.csv` with columns mapped for WooCommerce import, including:
- ID, Name, Description, Price
- Images (Photo 1-8 columns)
- Attributes (Material, Color, Brand, etc.)
