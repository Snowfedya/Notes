import pandas as pd
from typing import List
from models.product import Product
import os

def export_to_csv(products: List[Product], filename: str = "products.csv"):
    """
    Exports a list of Product objects to a CSV file formatted for WooCommerce import.
    """

    data = []
    for i, p in enumerate(products, 1):
        # Format images: comma separated
        images_str = ", ".join(p.images)

        # WooCommerce specific fields mapping could be complex,
        # but we follow the user's requested structure.

        row = {
            "ID": p.id,
            "Название товара": p.name,
            "Описание": p.description,
            "Цена (₽)": p.price,
            "Исходная цена (₽)": p.original_price if p.original_price else "",
            "Категория": p.category,
            "Подкатегория": p.subcategory,
            "Тип товара": p.product_type,
            "Материал": p.material,
            "Цвет": p.color,
            "Размер": p.size,
            "Совместимость": p.compatibility,
            "SKU": p.sku,
            "Артикул": p.vendor_code,
            "Бренд": p.brand,
            "Страна производства": p.country_of_origin,
            "Рейтинг": p.rating,
            "Количество отзывов": p.reviews_count,
            "В наличии": "Да" if p.in_stock else "Нет",
            "Срок доставки (дней)": p.delivery_days,
            "Стоимость доставки (₽)": p.delivery_cost,
            "Количество просмотров": p.views_count,
            "Дата добавления": p.date_added,
            "URL товара": p.url,
            "Источник маркетплейса": p.source,
            "Дополнительные характеристики": str(p.extra_data)
        }

        # Add separate columns for images as per "Фото 1-8" in prompt
        # Though usually WC imports comma-separated in one column "Images".
        # But the prompt table asks for "Photo 1" ... "Photo 8".
        # We will add them.
        for img_idx in range(8):
            key = f"Фото {img_idx + 1}"
            if img_idx < len(p.images):
                row[key] = p.images[img_idx]
            else:
                row[key] = ""

        data.append(row)

    df = pd.DataFrame(data)

    # Ensure column order matches the prompt
    columns_order = [
        "ID", "Название товара", "Описание", "Цена (₽)", "Исходная цена (₽)",
        "Категория", "Подкатегория", "Тип товара", "Материал", "Цвет",
        "Размер", "Совместимость", "SKU", "Артикул", "Бренд",
        "Страна производства", "Рейтинг", "Количество отзывов", "В наличии",
        "Срок доставки (дней)", "Стоимость доставки (₽)", "Количество просмотров",
        "Дата добавления"
    ]
    # Add Photo columns
    for k in range(1, 9):
        columns_order.append(f"Фото {k}")

    columns_order.extend(["URL товара", "Источник маркетплейса", "Дополнительные характеристики"])

    # Reorder if columns exist
    df = df[columns_order]

    # Save with UTF-8 BOM for Excel compatibility if needed, or just utf-8
    # Prompt asks for UTF-8.
    df.to_csv(filename, index=False, encoding="utf-8-sig")
    print(f"Successfully exported {len(products)} products to {filename}")
