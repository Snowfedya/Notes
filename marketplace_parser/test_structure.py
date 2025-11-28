import unittest
import os
import pandas as pd
from models.product import Product
from utils.csv_exporter import export_to_csv

class TestScraperStructure(unittest.TestCase):
    def test_product_model(self):
        p = Product(id="123", name="Test", description="Desc", price=100.0)
        self.assertEqual(p.id, "123")
        self.assertEqual(p.price, 100.0)

    def test_csv_export(self):
        products = [
            Product(
                id="TEST-001",
                name="Test Product",
                description="A test product",
                price=1000.0,
                original_price=1200.0,
                images=["http://example.com/1.jpg", "http://example.com/2.jpg"],
                category="Auto",
                brand="Toyota"
            )
        ]

        filename = "test_output.csv"
        export_to_csv(products, filename)

        self.assertTrue(os.path.exists(filename))

        df = pd.read_csv(filename)
        self.assertEqual(len(df), 1)
        self.assertEqual(df.iloc[0]["ID"], "TEST-001")
        self.assertEqual(df.iloc[0]["Фото 1"], "http://example.com/1.jpg")
        self.assertEqual(df.iloc[0]["Фото 2"], "http://example.com/2.jpg")
        self.assertEqual(df.iloc[0]["Бренд"], "Toyota")

        # Cleanup
        os.remove(filename)

if __name__ == '__main__':
    unittest.main()
