# Material Order APIs

All routes below are inside the `auth:api` middleware group — every request needs `Authorization: Bearer {token}`. Base URL: `/api`.

## What changed

A material order can now contain **several items** (e.g. Bricks + Sand + Cement in one order). All items of one order:
- are saved as separate rows in `material_orders`, sharing the same `order_group` id,
- print together on **one invoice PDF**,
- update the vendor's payable balance once (sum of all items).

The old single-item request format still works, so existing app versions keep working.

## Endpoints

| Method | Endpoint | Controller@Method | Purpose |
|---|---|---|---|
| POST | `/add-order` | `MaterialController@materialOrder` | Add a material order (one or many items). |
| GET | `/material-order/{id}/pdf` | `MaterialController@orderPdf` | Generate the purchase invoice PDF. Pass the id of **any** item — the PDF includes every item of its order. |
| POST | `/material/{siteId}/{materialType}` | `MaterialController@materialData` | List orders of a material type (each row now carries `order_group`). |
| POST | `/material-update/{id}` | `MaterialController@updateMaterial` | Edit one order row. |
| DELETE | `/material-delete/{id}` | `MaterialController@destroyMaterial` | Delete one order row. |

### `POST /add-order`

**Body** — `multipart/form-data` (use multipart when sending `attachment`).

Common fields:

| Field | Type | Required | Notes |
|---|---|---|---|
| `site_id` | int | yes | must exist in `sites` |
| `vendor_id` | int | yes | must exist in `vendors` |
| `material_type` | string | yes | e.g. `Bricks`, `Sand` |
| `date` | string | yes | **`d-m-Y`**, e.g. `19-09-2026` |
| `attachment` | file | no | jpg / jpeg / png / pdf, max 2 MB. Shared by every item of the order. |

**Multi-item format (new)** — send an `items` array:

| Field | Type | Required | Notes |
|---|---|---|---|
| `items[i][quantity]` | number | yes | |
| `items[i][price]` | number | yes | price **before** GST |
| `items[i][gst]` | number | no | GST percent, 0–100 |
| `items[i][unit]` | string | no | |
| `items[i][category_name]` | string | no | |
| `items[i][spec]` | string | no | spec / brand |
| `items[i][available_unit_count]` | number | no | |

Form-data example (2 items):
```
site_id=51
vendor_id=7
material_type=Bricks
date=19-09-2026
items[0][category_name]=Red Brick
items[0][spec]=Grade A
items[0][quantity]=1000
items[0][unit]=Nos
items[0][price]=8000
items[0][gst]=12
items[1][category_name]=Fly Ash Brick
items[1][quantity]=500
items[1][unit]=Nos
items[1][price]=3500
items[1][gst]=5
```

**Legacy single-item format (still supported)** — if `items` is **not** sent, the flat fields `quantity`, `price`, `gst`, `unit`, `category_name`, `spec`, `available_unit_count` are read exactly as before (`quantity` and `price` required).

**Response `200`**
```json
{
  "response_code": 200,
  "status": true,
  "message": "Material order added successfully.",
  "data": {
    "material_order": { "id": 101, "order_group": "b1c2...-uuid", "...": "first item, kept for older app versions" },
    "material_orders": [
      { "id": 101, "order_group": "b1c2...-uuid", "category_name": "Red Brick", "quantity": "1000", "price": "8000", "gst": "12", "total_amount": "8960" },
      { "id": 102, "order_group": "b1c2...-uuid", "category_name": "Fly Ash Brick", "quantity": "500", "price": "3500", "gst": "5", "total_amount": "3675" }
    ],
    "order_group": "b1c2...-uuid",
    "site_details": { "site_id": 51, "site_name": "...", "category_name": "Red Brick", "location": "...", "supervisor": { } },
    "vendor_details": { "id": 7, "name": "...", "mobile_no": "...", "address": "...", "email": "..." }
  }
}
```
`order_group` is `null` when the order has a single item.

**Errors**
- `422` `{ "status": "error", "errors": { "items.0.quantity": ["..."] } }` — validation failed (nothing is saved).
- `422` `{ "status": false, "message": "Invalid date format. Please use d-m-Y format." }`

All items are saved in one transaction — either every item is stored (and the vendor balance updated) or none.

### `GET /material-order/{id}/pdf`
`{id}` = `id` of any item in the order (e.g. `material_orders[0].id`).

**Response `200`**
```json
{
  "response_code": 200,
  "status": true,
  "message": "Purchase invoice generated successfully.",
  "data": { "pdf_url": "https://<host>/storage/material_orders/material_order_101.pdf" }
}
```
Multi-item orders return one PDF listing all items, so the app should call this **once per order** (use `material_orders[0].id`), not once per item.

`404` `{ "status": false, "message": "Material order not found." }`

## Deployment note
Run `php artisan migrate` on the server — it adds the nullable `order_group` column to `material_orders`.
