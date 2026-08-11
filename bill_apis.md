# Sales Bill & Purchase Bill APIs

All routes below are inside the `auth:api` middleware group — every request needs `Authorization: Bearer {token}`. Base URL: `/api`.

## Sales Bill

| Method | Endpoint | Controller@Method | Purpose |
|---|---|---|---|
| GET | `/sales-bill-form/{siteId}` | `SalesBillController@getDetails` | Get form pre-fill data before creating a bill: site, the site's active customer, and the Unit Master dropdown list. |
| GET | `/sales-bills/{siteId}` | `SalesBillController@index` | **Bill history** — all sales bills for a site, most recent first, each with its `details`. |
| POST | `/sales-bill-add` | `SalesBillController@store` | Create a sales bill + line items, generate & store the PDF, optionally email it. |
| GET | `/sales-bill/{id}` | `SalesBillController@show` | Fetch one sales bill with `details` and `site`. |
| DELETE | `/sales-bill-delete/{id}` | `SalesBillController@destroy` | Delete a sales bill (details + stored PDF). |

### `GET /sales-bill-form/{siteId}`
**Response**
```json
{
  "status": true,
  "message": "Sales bill form details fetched successfully.",
  "data": {
    "site": { "id": 51, "site_name": "...", "...": "..." },
    "customer": { "id": 3, "name": "...", "mobile_no": "...", "...": "..." },
    "units": ["Kg", "Bag", "Ton", "Cft"]
  }
}
```

### `GET /sales-bills/{siteId}`
**Response**
```json
{
  "status": true,
  "message": "Sales bills fetched successfully.",
  "data": [
    {
      "id": 12,
      "site_id": 51,
      "name": "Customer Name",
      "subject": "...",
      "date": "2026-08-10",
      "mobile_no": "9876543210",
      "email": "...",
      "location": "...",
      "total_amount": "2000.00",
      "terms_conditions": "...",
      "details": [
        { "id": 30, "particular": "Bricks", "count": "2.00", "unit": "Bag", "amount": "1000.00" }
      ]
    }
  ]
}
```

### `POST /sales-bill-add`
**Body** (`multipart/form-data` or `x-www-form-urlencoded`)
| Field | Type | Required | Notes |
|---|---|---|---|
| `site_id` | int | yes | must exist in `sites` |
| `name` | string | yes | customer name |
| `subject` | string | yes | |
| `date` | date | yes | |
| `mobile_no` | string | no | exactly 10 digits |
| `location` | string | yes | |
| `email` | email | no | required if you want the mail sent |
| `terms_conditions` | string | no | shown on PDF |
| `particular[]` | string[] | yes | one per line item |
| `count[]` | numeric[] | yes | quantity per line item |
| `unit[]` | string[] | no | unit per line item (from Unit Master) |
| `amount[]` | numeric[] | yes | **rate per unit** — line total = `count × amount`, summed into `total_amount` |

**Response**
```json
{
  "status": true,
  "message": "Sales bill created successfully.",
  "data": {
    "sales_bill_id": 12,
    "pdf_url": "https://.../storage/sales_bills/sales_bill_12.pdf",
    "whatsapp_url": "https://wa.me/91XXXXXXXXXX?text=...",
    "total_amount": 2000,
    "mail_sent": false
  }
}
```
Mail is sent automatically whenever `email` is filled (no separate `action` param on the API, unlike the admin web form).

### `GET /sales-bill/{id}`
Returns `{ "status": true, "data": { ...bill, "details": [...], "site": {...} } }`, or 404 `{ "status": false, "message": "Sales bill not found." }`.

### `DELETE /sales-bill-delete/{id}`
Deletes the bill, its `details`, and the stored PDF file. Returns `{ "status": true, "message": "Sales bill deleted successfully." }`.

---

## Purchase Bill

| Method | Endpoint | Controller@Method | Purpose |
|---|---|---|---|
| GET | `/purchase-bill-form/{siteId}` | `PurchaseBillController@getDetails` | Get form pre-fill data: site + Unit Master dropdown list. |
| GET | `/purchase-bills/{siteId}` | `PurchaseBillController@index` | **Bill history** — all purchase bills for a site, most recent first, each with `details`. |
| POST | `/purchase-bill-add` | `PurchaseBillController@store` | Create a purchase bill + line items, generate & store the PDF, optionally email it. |
| GET | `/purchase-bill/{id}` | `PurchaseBillController@show` | Fetch one purchase bill with `details` and `site`. |
| PATCH | `/purchase-bill-update/{id}` | `PurchaseBillController@update` | Update a purchase bill's header fields, and optionally replace all its line items. |
| DELETE | `/purchase-bill-delete/{id}` | `PurchaseBillController@destroy` | Delete a purchase bill (+ its details). |

### `GET /purchase-bill-form/{siteId}`
```json
{
  "status": true,
  "message": "Purchase bill form details fetched successfully.",
  "data": {
    "site": { "id": 51, "site_name": "...", "...": "..." },
    "units": ["Kg", "Bag", "Ton", "Cft"]
  }
}
```

### `GET /purchase-bills/{siteId}`
Same shape as `/sales-bills/{siteId}` — array of purchase bills with `details`, vendor's `name` in the `name` field.

### `POST /purchase-bill-add`
Same body shape as Sales Bill's `store` (`site_id`, `name` = vendor name, `subject`, `date`, `mobile_no`, `location`, `email`, `terms_conditions`, `particular[]`, `count[]`, `unit[]`, `amount[]`). Same rate × count total logic. Response shape mirrors Sales Bill's `store` (`purchase_bill_id`, `pdf_url`, `whatsapp_url`, `total_amount`, `mail_sent`).

### `GET /purchase-bill/{id}`
Returns `{ "status": true, "data": { ...bill, "details": [...], "site": {...} } }`, or 404 if not found.

### `PATCH /purchase-bill-update/{id}`
**Body**
| Field | Type | Required |
|---|---|---|
| `name` | string | yes |
| `subject` | string | yes |
| `date` | date | yes |
| `mobile_no` | string | no |
| `location` | string | yes |
| `email` | email | no |
| `terms_conditions` | string | no |
| `particular[]` / `count[]` / `unit[]` / `amount[]` | arrays | no — **if `particular` is present, ALL existing line items are deleted and replaced** with the submitted set (and `total_amount` recalculated); omit these fields entirely to leave line items untouched. |

Returns the updated bill with `details`.

### `DELETE /purchase-bill-delete/{id}`
Deletes the bill and its `details`. Note: unlike Sales Bill's destroy, this one does **not** delete the stored PDF file from disk. Returns `{ "status": true, "message": "Purchase bill deleted successfully." }`.

---

## Notes / asymmetries worth knowing

- **Sales Bill has no `update` endpoint** — Purchase Bill does. If mobile needs to edit a sales bill, that's not built yet.
- **Sales Bill's `destroy` cleans up the stored PDF file; Purchase Bill's `destroy` does not.** Minor inconsistency, flagging in case it matters for storage cleanup.
- `amount[]` is the **rate per unit**, not the line total — the server computes `total_amount = Σ(count × amount)` per bill. `count` defaults to a ×1 multiplier if zero/blank, so a lump-sum particular with no quantity still totals correctly as just the rate.
- Mail is sent whenever `email` is present in the request — there's no `send_mail` flag to opt out on the API side (the admin web form has an explicit `action=mail` choice; the API always sends if email is filled).
