<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .menu-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div class="menu-container text-white shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">{{ config('app.name', 'Restaurant') }}</h1>
                    <p class="text-purple-100">Menu</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Category Filter -->
        <div class="mb-8">
            <div class="flex gap-3 overflow-x-auto pb-4">
                <button class="category-btn px-6 py-2 rounded-full font-semibold whitespace-nowrap transition bg-purple-600 text-white" data-category="all">
                    All Items
                </button>
                @foreach($categories as $category)
                    <button class="category-btn px-6 py-2 rounded-full font-semibold whitespace-nowrap transition bg-white text-gray-700 hover:bg-purple-100" data-category="{{ $category->id }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-20" id="productsContainer">
            <!-- Products will be loaded here -->
        </div>
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
                        b.classList.remove('bg-purple-600', 'text-white');
                        b.classList.add('bg-white', 'text-gray-700', 'hover:bg-purple-100');
                    });
                    this.classList.remove('bg-white', 'text-gray-700', 'hover:bg-purple-100');
                    this.classList.add('bg-purple-600', 'text-white');
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
                container.innerHTML = '<div class="col-span-full text-center py-12"><p class="text-gray-500 text-lg">No products available</p></div>';
                return;
            }

            productList.forEach(product => {
                const html = `
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                        ${product.image ? `<img src="/storage/${product.image}" alt="${product.name}" class="w-full h-48 object-cover">` : '<div class="w-full h-48 bg-gray-200 flex items-center justify-center"><i class="fas fa-image text-gray-400 text-4xl"></i></div>'}
                        <div class="p-4">
                            <h3 class="text-lg font-bold text-gray-800 mb-2">${product.name}</h3>
                            ${product.description ? `<p class="text-gray-600 text-sm mb-3">${product.description}</p>` : ''}
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-bold text-purple-600">Rs. ${parseFloat(product.price).toFixed(2)}</span>
                                ${!product.is_unlimited_stock ? `<span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded">Stock: ${product.quantity}</span>` : '<span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Available</span>'}
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
