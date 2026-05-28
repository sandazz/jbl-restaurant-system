# QR Menu - Digital Menu & Online Ordering System

A complete QR code-based menu system that allows customers to scan a code and browse your restaurant menu, then place orders online directly from their mobile devices.

## Features

✅ **QR Code Generation** - Automatically generates scannable QR codes for your menu
✅ **Digital Menu Display** - Mobile-responsive menu browsing interface
✅ **Online Ordering** - Customers can add items to cart and place orders
✅ **Category Filtering** - Browse products by category
✅ **Stock Management** - Shows product availability
✅ **Download Options** - PNG images and PDF for printing
✅ **No Authentication Required** - Customers don't need to log in to order
✅ **Multiple Placement Formats** - Different sizes for different use cases

## Quick Start

### 1. Access QR Menu Management

1. Log in to your admin panel
2. Go to **Settings** → **QR Menu**
3. You'll see your generated QR code

### 2. Download QR Code

Choose one of the download options:
- **Download as PNG** - For digital sharing or custom printing
- **Print Ready PDF** - Complete printing guide with multiple sizes
- **Copy Menu URL** - Share the direct link with customers

### 3. Print & Deploy

The generated PDF includes:
- Full-size QR code (300x300px)
- Recommended sizes for different placements
- Printing instructions
- Placement guide

### 4. Placement Ideas

**On Tables:**
- Print on table tents (3" × 3" recommended)
- Customers can scan while waiting or eating
- Encourages additional orders and upselling

**At Entrance:**
- Display large QR code poster (8" × 8")
- Attracts foot traffic
- Lets customers pre-browse before being seated

**On Menu Covers:**
- Print on physical menu covers (2" × 2")
- First thing customers see
- Encourages immediate engagement

**With Receipts:**
- Include on order receipts (1" × 1")
- Drives repeat online orders
- Good for future visits

**On Windows:**
- Large format displays
- Visible from street
- Promotes online ordering to passersby

**Social Media:**
- Share menu URL: `{{ app.url }}/menu/scan`
- Use in marketing materials
- Drive traffic from digital channels

## How It Works for Customers

1. **Scan** - Customer points phone camera at QR code
2. **Browse** - Opens responsive menu with product listings
3. **Filter** - Browse by category or search products
4. **Add** - Add items to shopping cart
5. **Checkout** - Enter name, phone, order type
6. **Order** - Order placed and appears in your POS system

## Technical Details

### Routes

**Public Routes (No Authentication Required):**
```
GET  /menu/scan                    - View menu and place orders
GET  /api/menu/category/:id        - Fetch products by category
GET  /qr-code/generate             - Generate QR code image
GET  /qr-code/download             - Download QR code PNG
GET  /qr-code/pdf                  - Download printable PDF
```

**Admin Routes (Authentication Required):**
```
GET  /qr-menu/admin                - QR management dashboard
```

### API Endpoints

#### View Menu
```
GET /menu/scan
```
Returns HTML page with interactive menu.

#### Get Products by Category
```
GET /api/menu/category/{categoryId}
// categoryId = 'all' for all products, or numeric category ID
```

Returns JSON:
```json
[
  {
    "id": 1,
    "name": "Burger",
    "description": "Classic burger",
    "price": 12.99,
    "category_id": 1,
    "category_name": "Main Courses",
    "image": "/path/to/image.jpg",
    "is_unlimited_stock": false,
    "quantity": 45
  }
]
```

#### Place Order
```
POST /pos/order
```

Request Body:
```json
{
  "order_type": "takeaway|delivery|dine_in|vip_room",
  "customer_name": "John Doe",
  "customer_phone": "+1234567890"
}
```

Response:
```json
{
  "success": true,
  "order_id": 1,
  "order_number": "ORD-ABC123XY"
}
```

#### Add Items to Order
```
POST /pos/order/{order_id}/item
```

Request Body:
```json
{
  "product_id": 1,
  "quantity": 2,
  "kitchen_notes": "No onions"
}
```

### Files Created

#### Controllers
- `app/Http/Controllers/QrMenuController.php` - Handles QR code generation and menu display

#### Views
- `resources/views/qr-menu/menu.blade.php` - Customer-facing menu interface
- `resources/views/qr-menu/qr-admin.blade.php` - Admin QR management dashboard
- `resources/views/qr-menu/qr-pdf.blade.php` - Printable PDF with sizes guide

#### Routes
- Public QR routes in `routes/web.php`
- Admin QR route in `routes/web.php`

## Menu Features

### Product Display
- Product image (if available)
- Product name and description
- Price display
- Stock status (if limited stock enabled)

### Shopping Cart
- Add/remove items
- Quantity adjustment
- Real-time cart count
- Cart total calculation

### Customer Information
- Customer name (required)
- Customer phone (required)
- Order type selection (Takeaway/Delivery/Dine In/VIP Room)
- Order summary before checkout

### Integration with POS
- Automatically creates order in POS system
- Adds all items to order
- Updates table status (if applicable)
- Order appears immediately for kitchen preparation

## Customization

### Colors & Styling
Edit `resources/views/qr-menu/menu.blade.php` to customize:
- Header colors (currently purple gradient)
- Button colors and styles
- Cart modal appearance
- Product grid layout

### Menu URL
Default: `{{ app_url }}/menu/scan`

To customize, update the routes in `routes/web.php`:
```php
Route::get('/your-custom-path/scan', [QrMenuController::class, 'viewMenu'])->name('menu.view');
```

### QR Code Size
The QR code automatically scales to different print sizes. All sizes are tested and work perfectly with standard smartphone cameras.

## Testing

### Test the Menu
1. Visit `/menu/scan` directly in your browser
2. On mobile: scan the generated QR code
3. Try adding items and placing an order

### Verify Order Creation
1. Go to POS or Orders section
2. Check that your test order appears
3. Verify all items were added correctly

## Troubleshooting

### QR Code Not Scanning
- Ensure QR code has at least 2-3cm (1 inch) margin around it
- Ensure print quality is high (dark QR on white background)
- Test with multiple phones
- Verify the URL in QR code matches your app URL

### Menu Not Loading
- Check that all products are set to "active" status
- Verify categories are set to "active"
- Check browser console for JavaScript errors
- Clear browser cache

### Orders Not Appearing in POS
- Verify customer information was filled in
- Check that order creation response shows "success": true
- Check POS system is running and accepting orders
- Review Laravel logs for any errors

### Image Not Displaying
- Ensure product images are uploaded
- Check image file paths are correct
- Verify image files exist in storage

## Dependencies

- Laravel Framework 12.0+
- PHP 8.2+
- `endroid/qr-code` (already installed)

## Security Notes

✅ **No Database Storage** - QR Code is generated on-the-fly
✅ **Public Menu** - No authentication required (by design)
✅ **CSRF Protection** - All POST requests protected
✅ **Input Validation** - All customer input validated
✅ **Rate Limiting** - Consider adding if high traffic expected

## Performance Optimization

### QR Code Generation
- QR codes are generated in-memory (no file storage)
- Browser caching enabled (3600 seconds)
- PNG encoding for optimal file size

### Menu Loading
- Products lazy-loaded by category
- Images optimized for mobile
- Responsive grid layout

## Future Enhancements

- [ ] Multiple menu QR codes (breakfast, lunch, dinner)
- [ ] Special dietary filters (vegetarian, vegan, etc.)
- [ ] Customer rating & reviews
- [ ] Customizable menu URL slugs
- [ ] Email order confirmations
- [ ] Order tracking for delivery orders
- [ ] Integration with payment gateways
- [ ] Analytics and sales reports

## Support

For issues or questions about the QR Menu feature, check:
1. This guide
2. Laravel logs in `storage/logs/`
3. Browser developer console (F12)
4. Application error messages

## Example Use Cases

**Quick Service Restaurant**
- Place large QR codes on table
- Customers order without waiting for server
- Reduces service time
- Increases order accuracy

**Café/Coffee Shop**
- Display at counter for takeaway orders
- Customers order on phone while waiting
- Reduces counter congestion
- Faster service

**Fine Dining**
- Premium table tents with QR
- Customers browse between courses
- Digital wine/drink recommendations
- Contactless ordering

**Food Truck**
- Display on window with window decal
- Attracts customers with digital menu
- Quick transactions
- No printed menus needed

**Delivery Partner**
- Include QR code in marketing
- Easy menu access for customers
- Drive online orders
- Track successful channels

---

**Generated:** {{ date('Y-m-d H:i:s') }}
**Version:** 1.0.0
