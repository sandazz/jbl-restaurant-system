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

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            animation: slideDown 0.6s ease-out;
        }

        .logo-img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            border: 4px solid white;
            transition: transform 0.3s ease;
        }

        .logo-img:hover {
            transform: scale(1.05);
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: 1px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header p {
            color: #e0c3fc;
            font-size: 0.95rem;
            font-weight: 500;
            margin-top: 2px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Category Filter Styles */
        .category-filter {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding: 20px 0;
            scroll-behavior: smooth;
        }

        .category-filter::-webkit-scrollbar {
            height: 6px;
        }

        .category-filter::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        .category-filter::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }

        .category-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            color: #333;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid transparent;
        }

        .category-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }

        .category-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.4);
        }

        /* Product Card Styles */
        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 40px rgba(102, 126, 234, 0.3);
        }

        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            position: relative;
            overflow: hidden;
        }

        .product-image-placeholder {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image-placeholder i {
            font-size: 3rem;
            color: #cbd5e1;
        }

        .product-info {
            padding: 18px;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .product-description {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 12px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stock-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .stock-available {
            background: #d1fae5;
            color: #047857;
        }

        .stock-limited {
            background: #fef3c7;
            color: #b45309;
        }

        /* Empty State */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 16px;
        }

        .empty-state p {
            font-size: 1.1rem;
            color: #64748b;
            font-weight: 500;
        }

        /* Container */
        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 16px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.75rem;
            }

            .logo-container {
                gap: 1rem;
            }

            .logo-img {
                width: 55px;
                height: 55px;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 16px;
            }

            .category-btn {
                padding: 10px 18px;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.3rem;
            }

            .logo-container {
                flex-direction: column;
                text-align: center;
                gap: 0.75rem;
            }

            .logo-img {
                width: 50px;
                height: 50px;
            }

            .header p {
                font-size: 0.85rem;
            }

            .products-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .product-price {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="content-container">
            <div class="py-6">
                <div class="logo-container">
                    <img src="/images/logo.png" alt="JBL Food Corner Logo" class="logo-img">
                    <div>
                        <h1>JBL FOOD CORNER</h1>
                        <p>Discover Our Delicious Menu</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-container py-8">
        <!-- Category Filter -->
        <div class="mb-8">
            <div class="category-filter" id="categoryFilter">
                <button class="category-btn active" data-category="all">
                    <i class="fas fa-th mr-2"></i>All Items
                </button>
                @foreach($categories as $category)
                    <button class="category-btn" data-category="{{ $category->id }}">
                        <i class="fas fa-tag mr-2"></i>{{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Products Grid -->
        <div class="products-grid" id="productsContainer">
            <!-- Products will be loaded here -->
        </div>
    </div>


    <script>
        let currentCategory = 'all';
        let animationDelay = 0;

        document.addEventListener('DOMContentLoaded', function() {
            loadProducts('all');
            setupEventListeners();
        });

        function setupEventListeners() {
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.category-btn').forEach(b => {
                        b.classList.remove('active');
                    });
                    this.classList.add('active');
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
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-inbox"></i>
                        <p>No products available in this category</p>
                    </div>
                `;
                return;
            }

            productList.forEach((product, index) => {
                animationDelay = index * 50;
                const stockStatus = !product.is_unlimited_stock && product.quantity < 10 ? 'stock-limited' : 'stock-available';
                const stockText = !product.is_unlimited_stock ? `Stock: ${product.quantity}` : 'Available';

                const html = `
                    <div class="product-card" style="animation-delay: ${animationDelay}ms;">
                        ${product.image ? `<img src="/storage/${product.image}" alt="${product.name}" class="product-image">` : '<div class="product-image-placeholder"><i class="fas fa-image"></i></div>'}
                        <div class="product-info">
                            <h3 class="product-name">${product.name}</h3>
                            ${product.description ? `<p class="product-description">${product.description}</p>` : ''}
                            <div class="product-footer">
                                <span class="product-price">Rs. ${parseFloat(product.price).toFixed(2)}</span>
                                <span class="stock-badge ${stockStatus}">${stockText}</span>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += html;
            });
        }
    </script>
</body>
</html>
