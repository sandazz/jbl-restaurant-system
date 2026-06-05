<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JBL Food Corner - Digital Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
        }

        .menu-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            animation: slideInDown 0.6s ease-out;
        }

        .logo-container img {
            height: 70px;
            width: 70px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease;
        }

        .logo-container img:hover {
            transform: scale(1.05);
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .category-btn {
            transition: all 0.3s ease;
            position: relative;
            overflow: visible;
            border: none;
            cursor: pointer;
            color: #4b5563;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .category-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .category-btn:hover::before {
            left: 0;
        }

        .category-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            transform: translateY(-2px);
            color: white;
        }

        .product-card {
            animation: fadeInUp 0.5s ease-out;
            overflow: hidden;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
        }

        .product-image-container {
            position: relative;
            overflow: hidden;
            height: 200px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .product-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .product-card:hover .product-image-container img {
            transform: scale(1.1);
        }

        .badge-stock {
            position: absolute;
            top: 12px;
            right: 12px;
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            animation: slideIn 0.3s ease-out;
        }

        .badge-stock.available {
            background: rgba(34, 197, 94, 0.85);
            color: white;
            border: 1px solid rgba(34, 197, 94, 0.5);
        }

        .badge-stock.limited {
            background: rgba(251, 146, 60, 0.85);
            color: white;
            border: 1px solid rgba(251, 146, 60, 0.5);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .product-info {
            padding: 20px;
            background: white;
        }

        .product-name {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .product-description {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 12px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
        }

        .product-price {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .add-to-cart-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .add-to-cart-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .add-to-cart-btn:active {
            transform: scale(0.98);
        }

        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
        }

        .no-products-icon {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .no-products-text {
            color: #9ca3af;
            font-size: 18px;
            font-weight: 500;
        }

        .category-filter-section {
            margin-bottom: 32px;
            animation: fadeInUp 0.6s ease-out;
        }

        .category-label {
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 12px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .category-scroll {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 8px 0;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .category-scroll::-webkit-scrollbar {
            height: 6px;
        }

        .category-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .category-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }

        .category-scroll::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .logo-container {
                flex-direction: column;
                text-align: center;
                gap: 0.75rem;
            }

            .logo-container h1 {
                font-size: 24px;
                font-weight: 700;
            }

            .logo-container p {
                font-size: 12px;
            }

            .logo-container img {
                height: 56px;
                width: 56px;
            }

            .max-w-7xl {
                padding: 16px 12px;
            }

            .product-card {
                margin: 0 auto;
            }

            .category-btn {
                padding: 8px 16px;
                font-size: 14px;
            }

            .product-name {
                font-size: 16px;
            }

            .product-price {
                font-size: 22px;
            }
        }

        @media (max-width: 640px) {
            .logo-container h1 {
                font-size: 20px;
            }

            .menu-container {
                padding: 0;
            }

            .category-btn {
                padding: 10px 14px;
                font-size: 13px;
            }

            .product-info {
                padding: 16px;
            }

            .product-footer {
                flex-wrap: wrap;
                gap: 8px;
            }
        }

        .loading-skeleton {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Header -->
    <div class="menu-container text-white sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-6 sm:py-8">
            <div class="flex items-center justify-center">
                <div class="logo-container">
                    <img src="/images/logo.png" alt="JBL Food Corner Logo" class="h-16 w-16 sm:h-20 sm:w-20 rounded-full object-cover shadow-lg">
                    <div class="text-center sm:text-left">
                        <h1 class="text-2xl sm:text-4xl font-bold tracking-tight">JBL FOOD CORNER</h1>
                        <p class="text-purple-100 text-sm sm:text-base mt-1 font-medium">✨ Digital Menu Experience</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8 sm:py-12">
        <!-- Category Filter -->
        <div class="category-filter-section">
            <span class="category-label"><i class="fas fa-filter mr-2"></i>Browse Categories</span>
            <div class="category-scroll">
                <button class="category-btn px-4 sm:px-6 py-2.5 sm:py-3 rounded-full font-semibold whitespace-nowrap active bg-gradient-to-r from-purple-600 to-purple-800 text-white" data-category="all">
                    <i class="fas fa-th mr-2"></i>All Items
                </button>
                @foreach($categories as $category)
                    <button class="category-btn px-4 sm:px-6 py-2.5 sm:py-3 rounded-full font-semibold whitespace-nowrap bg-white text-gray-700 shadow-sm hover:shadow-md" data-category="{{ $category->id }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 mb-16 sm:mb-20" id="productsContainer">
            <!-- Products will be loaded here -->
        </div>
    </div>

    <!-- Footer -->
    <div class="bg-gradient-to-r from-purple-900 to-purple-700 text-white py-8 text-center">
        <p class="text-sm sm:text-base opacity-90">
            <i class="fas fa-utensils mr-2"></i> Enjoy our delicious menu | <i class="fas fa-phone ml-4 mr-2"></i> Call us for orders
        </p>
    </div>


    <script>
        let currentCategory = 'all';

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts('all');
            setupEventListeners();
        });

        function setupEventListeners() {
            // Category filter
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.category-btn').forEach(b => {
                        b.classList.remove('active', 'bg-gradient-to-r', 'from-purple-600', 'to-purple-800', 'text-white', 'shadow-lg');
                        b.classList.add('bg-white', 'text-gray-700', 'shadow-sm');
                    });
                    this.classList.add('active', 'bg-gradient-to-r', 'from-purple-600', 'to-purple-800', 'text-white', 'shadow-lg');
                    this.classList.remove('bg-white', 'text-gray-700', 'shadow-sm');
                    currentCategory = this.dataset.category;
                    loadProducts(currentCategory);
                });
            });
        }

        function loadProducts(categoryId) {
            const url = categoryId === 'all'
                ? '/api/menu/category/all'
                : `/api/menu/category/${categoryId}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    displayProducts(data);
                })
                .catch(error => console.error('Error loading products:', error));
        }

        function displayProducts(productList) {
            const container = document.getElementById('productsContainer');
            container.innerHTML = '';

            if (productList.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full">
                        <div class="no-products">
                            <div class="no-products-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <p class="no-products-text">No items available in this category</p>
                        </div>
                    </div>
                `;
                return;
            }

            productList.forEach((product, index) => {
                const stockBadge = !product.is_unlimited_stock 
                    ? `<span class="badge-stock limited"><i class="fas fa-warning text-xs mr-1"></i>Stock: ${product.quantity}</span>`
                    : '<span class="badge-stock available"><i class="fas fa-check-circle text-xs mr-1"></i>Available</span>';

                const html = `
                    <div class="product-card bg-white rounded-lg shadow-lg" style="animation-delay: ${index * 0.05}s;">
                        <div class="product-image-container relative">
                            ${product.image 
                                ? `<img src="/storage/${product.image}" alt="${product.name}" loading="lazy">` 
                                : '<div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300"><i class="fas fa-image text-gray-400 text-5xl"></i></div>'
                            }
                            ${stockBadge}
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">${product.name}</h3>
                            ${product.description ? `<p class="product-description">${product.description}</p>` : ''}
                            <div class="product-footer">
                                <span class="product-price">Rs. ${parseFloat(product.price).toFixed(2)}</span>
                                <button class="add-to-cart-btn">
                                    <i class="fas fa-shopping-cart mr-1"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += html;
            });

            // Add click handlers to add-to-cart buttons if needed
            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Add to cart clicked');
                    // Add your cart functionality here
                });
            });
        }
    </script>
</body>
</html>
