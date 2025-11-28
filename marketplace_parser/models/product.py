from dataclasses import dataclass, field
from typing import List, Dict, Optional

@dataclass
class Product:
    id: str
    name: str
    description: str
    price: float
    original_price: Optional[float] = None
    url: str = ""
    images: List[str] = field(default_factory=list)

    # Categories
    category: str = ""
    subcategory: str = ""
    product_type: str = ""

    # Attributes
    material: str = ""
    color: str = ""
    size: str = ""
    compatibility: str = ""

    # Tech Specs
    sku: str = ""
    vendor_code: str = "" # Артикул
    brand: str = ""
    country_of_origin: str = ""

    # Stats
    rating: float = 0.0
    reviews_count: int = 0
    in_stock: bool = True
    delivery_days: int = 0
    delivery_cost: float = 0.0
    views_count: int = 0
    date_added: str = ""

    # Metadata
    source: str = "" # Wildberries or Avito
    extra_data: Dict = field(default_factory=dict)
