// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    feather.replace();
    
    // Initialize navigation
    initNavigation();
    
    // Load initial data
    loadDashboardData();
    loadProducts();
    loadRawMaterials();
    loadSuppliers();
    loadCategories();
    
    // Initialize event listeners
    initEventListeners();
});

// Initialize navigation
function initNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links and sections
            navLinks.forEach(l => l.classList.remove('active'));
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Show corresponding section
            const sectionId = this.getAttribute('data-section');
            document.getElementById(sectionId).classList.add('active');
        });
    });
}

// Initialize event listeners
function initEventListeners() {
    // Add product form submission
    document.getElementById('add-product-form').addEventListener('submit', function(e) {
        e.preventDefault();
        addProduct();
    });
    
    // Add raw material form submission
    document.getElementById('add-raw-material-form').addEventListener('submit', function(e) {
        e.preventDefault();
        addRawMaterial();
    });
    
    // Update stock form submission
    document.getElementById('update-stock-form').addEventListener('submit', function(e) {
        e.preventDefault();
        updateStock();
    });
}

// Load dashboard data
function loadDashboardData() {
    console.log('Loading dashboard data...');
    fetch('../backend/api/get_dashboard_data.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            console.log('Dashboard response received');
            return response.json();
        })
        .then(data => {
            console.log('Dashboard data:', data);
            if (data.success) {
                // Update dashboard cards
                document.getElementById('total-products').textContent = data.data.total_products;
                document.getElementById('total-categories').textContent = data.data.total_categories;
                document.getElementById('total-materials').textContent = data.data.total_materials;
                document.getElementById('low-stock-count').textContent = data.data.low_stock_count;
                
                // Update low stock table
                const lowStockTable = document.getElementById('low-stock-table');
                lowStockTable.innerHTML = '';
                
                if (data.data.low_stock_materials.length === 0) {
                    lowStockTable.innerHTML = '<tr><td colspan="4" class="text-center">No low stock materials</td></tr>';
                } else {
                    data.data.low_stock_materials.forEach(material => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${material.Material_Name}</td>
                            <td class="low-stock">${material.Stock_Quantity}</td>
                            <td>${material.Reorder_Level}</td>
                            <td>${material.Supplier_Name || 'N/A'}</td>
                        `;
                        lowStockTable.appendChild(row);
                    });
                }
                
                // Create category chart
                createCategoryChart(data.data.category_distribution);
            } else {
                console.error('Dashboard data error:', data.message);
                showAlert('Error loading dashboard data: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
            console.error('Error details:', error.stack);
            showAlert('Error loading dashboard data: ' + error.message, 'danger');
        });
}

// Create category chart
function createCategoryChart(categoryData) {
    console.log('Creating category chart with data:', categoryData);
    
    // Check if Chart object is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        document.getElementById('categoryChart').parentNode.innerHTML = '<div class="alert alert-danger">Chart.js library not loaded. Please refresh the page.</div>';
        return;
    }
    
    const canvas = document.getElementById('categoryChart');
    if (!canvas) {
        console.error('Chart canvas element not found');
        return;
    }
    
    // Make sure the context is available
    let ctx;
    try {
        ctx = canvas.getContext('2d');
    } catch (error) {
        console.error('Could not get canvas context:', error);
        return;
    }
    
    // If there's an existing chart, destroy it
    if (window.categoryChart) {
        try {
            window.categoryChart.destroy();
        } catch (e) {
            console.warn('Error destroying previous chart:', e);
        }
    }
    
    if (!categoryData || !Array.isArray(categoryData) || categoryData.length === 0) {
        console.log('No category data available');
        canvas.parentNode.innerHTML = '<div class="alert alert-info">No category data available</div>';
        return;
    }
    
    try {
        // Process the data
        const labels = categoryData.map(item => item.category || 'Unknown');
        const counts = categoryData.map(item => parseInt(item.count) || 0);
        
        console.log('Chart labels:', labels);
        console.log('Chart data:', counts);
        
        // Setup colors (ensure we have enough)
        const backgroundColors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#5a5c69', '#858796', '#f8f9fc', '#d1d3e2', '#b7b9cc'
        ];
        
        const hoverBackgroundColors = [
            '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617',
            '#3a3b45', '#60616f', '#d8daeb', '#a8aabe', '#989bb3'
        ];
        
        // Create the chart
        window.categoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: backgroundColors.slice(0, labels.length),
                    hoverBackgroundColor: hoverBackgroundColors.slice(0, labels.length),
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                return label + ': ' + value + ' products';
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
        console.log('Chart created successfully');
    } catch (error) {
        console.error('Error creating chart:', error);
        console.error('Error details:', error.stack);
        canvas.parentNode.innerHTML = '<div class="alert alert-danger">Error creating chart: ' + error.message + '</div>';
    }
}

// Load products
function loadProducts() {
    fetch('../backend/api/get_products.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const productsTable = document.getElementById('products-table');
                productsTable.innerHTML = '';
                
                if (data.data.length === 0) {
                    productsTable.innerHTML = '<tr><td colspan="6" class="text-center">No products found</td></tr>';
                } else {
                    data.data.forEach(product => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${product.Product_ID}</td>
                            <td>${product.Product_Name}</td>
                            <td>${product.Category_Name || 'N/A'}</td>
                            <td>$${parseFloat(product.Manufacturing_Cost).toFixed(2)}</td>
                            <td>$${parseFloat(product.Selling_Price).toFixed(2)}</td>
                            <td>$${parseFloat(product.Profit_Margin).toFixed(2)}</td>
                        `;
                        productsTable.appendChild(row);
                    });
                }
            } else {
                showAlert('Error loading products: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading products:', error);
            showAlert('Error loading products: ' + error.message, 'danger');
        });
}

// Load raw materials
function loadRawMaterials() {
    fetch('../backend/api/get_raw_materials.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const materialsTable = document.getElementById('materials-table');
                materialsTable.innerHTML = '';
                
                if (data.data.length === 0) {
                    materialsTable.innerHTML = '<tr><td colspan="7" class="text-center">No raw materials found</td></tr>';
                } else {
                    data.data.forEach(material => {
                        const row = document.createElement('tr');
                        const isLowStock = material.Low_Stock;
                        
                        row.innerHTML = `
                            <td>${material.Material_ID}</td>
                            <td>${material.Material_Name}</td>
                            <td>${material.Supplier_Name || 'N/A'}</td>
                            <td class="${isLowStock ? 'low-stock' : ''}">${material.Stock_Quantity}</td>
                            <td>$${parseFloat(material.Unit_Price).toFixed(2)}</td>
                            <td>${material.Reorder_Level}</td>
                            <td>
                                <button class="btn btn-sm btn-primary update-stock-btn" 
                                    data-id="${material.Material_ID}" 
                                    data-name="${material.Material_Name}" 
                                    data-quantity="${material.Stock_Quantity}">
                                    Update Stock
                                </button>
                            </td>
                        `;
                        materialsTable.appendChild(row);
                    });
                    
                    // Add event listeners for update stock buttons
                    document.querySelectorAll('.update-stock-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            openUpdateStockModal(
                                this.getAttribute('data-id'),
                                this.getAttribute('data-name'),
                                this.getAttribute('data-quantity')
                            );
                        });
                    });
                }
            } else {
                showAlert('Error loading raw materials: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading raw materials:', error);
            showAlert('Error loading raw materials: ' + error.message, 'danger');
        });
}

// Load suppliers
function loadSuppliers() {
    fetch('../backend/api/get_suppliers.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const suppliersTable = document.getElementById('suppliers-table');
                suppliersTable.innerHTML = '';
                
                if (data.data.length === 0) {
                    suppliersTable.innerHTML = '<tr><td colspan="4" class="text-center">No suppliers found</td></tr>';
                } else {
                    data.data.forEach(supplier => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${supplier.Supplier_ID}</td>
                            <td>${supplier.Supplier_Name}</td>
                            <td>${supplier.Contact_Info}</td>
                            <td>${supplier.Address}</td>
                        `;
                        suppliersTable.appendChild(row);
                    });
                }
                
                // Populate supplier dropdown in add raw material form
                const supplierSelect = document.getElementById('material-supplier');
                supplierSelect.innerHTML = '';
                
                data.data.forEach(supplier => {
                    const option = document.createElement('option');
                    option.value = supplier.Supplier_ID;
                    option.textContent = supplier.Supplier_Name;
                    supplierSelect.appendChild(option);
                });
            } else {
                showAlert('Error loading suppliers: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading suppliers:', error);
            showAlert('Error loading suppliers: ' + error.message, 'danger');
        });
}

// Load categories
function loadCategories() {
    fetch('../backend/api/get_categories.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Populate category dropdown in add product form
                const categorySelect = document.getElementById('product-category');
                categorySelect.innerHTML = '';
                
                data.data.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.Category_ID;
                    option.textContent = category.Category_Name;
                    categorySelect.appendChild(option);
                });
            } else {
                showAlert('Error loading categories: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
            showAlert('Error loading categories: ' + error.message, 'danger');
        });
}

// Add product
function addProduct() {
    const productName = document.getElementById('product-name').value;
    const categoryId = document.getElementById('product-category').value;
    const manufacturingCost = document.getElementById('manufacturing-cost').value;
    const sellingPrice = document.getElementById('selling-price').value;
    
    // Validate form data
    if (!productName || !categoryId || !manufacturingCost || !sellingPrice) {
        showAlert('Please fill all required fields', 'warning');
        return;
    }
    
    if (parseFloat(manufacturingCost) <= 0) {
        showAlert('Manufacturing cost must be greater than zero', 'warning');
        return;
    }
    
    if (parseFloat(sellingPrice) <= 0) {
        showAlert('Selling price must be greater than zero', 'warning');
        return;
    }
    
    const productData = {
        Product_Name: productName,
        Category_ID: parseInt(categoryId),
        Manufacturing_Cost: parseFloat(manufacturingCost),
        Selling_Price: parseFloat(sellingPrice)
    };
    
    fetch('../backend/api/add_product.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(productData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
            modal.hide();
            
            // Reset form
            document.getElementById('add-product-form').reset();
            
            // Show success message
            showAlert('Product added successfully', 'success');
            
            // Reload products and dashboard data
            loadProducts();
            loadDashboardData();
        } else {
            showAlert('Error adding product: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error adding product:', error);
        showAlert('Error adding product: ' + error.message, 'danger');
    });
}

// Add raw material
function addRawMaterial() {
    const materialName = document.getElementById('material-name').value;
    const supplierId = document.getElementById('material-supplier').value;
    const stockQuantity = document.getElementById('stock-quantity').value;
    const unitPrice = document.getElementById('unit-price').value;
    const reorderLevel = document.getElementById('reorder-level').value;
    
    // Validate form data
    if (!materialName || !supplierId || !stockQuantity || !unitPrice || !reorderLevel) {
        showAlert('Please fill all required fields', 'warning');
        return;
    }
    
    if (parseInt(stockQuantity) < 0) {
        showAlert('Stock quantity cannot be negative', 'warning');
        return;
    }
    
    if (parseFloat(unitPrice) <= 0) {
        showAlert('Unit price must be greater than zero', 'warning');
        return;
    }
    
    if (parseInt(reorderLevel) < 0) {
        showAlert('Reorder level cannot be negative', 'warning');
        return;
    }
    
    const materialData = {
        Material_Name: materialName,
        Supplier_ID: parseInt(supplierId),
        Stock_Quantity: parseInt(stockQuantity),
        Unit_Price: parseFloat(unitPrice),
        Reorder_Level: parseInt(reorderLevel)
    };
    
    fetch('../backend/api/add_raw_material.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(materialData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addRawMaterialModal'));
            modal.hide();
            
            // Reset form
            document.getElementById('add-raw-material-form').reset();
            
            // Show success message
            showAlert('Raw material added successfully', 'success');
            
            // Reload raw materials and dashboard data
            loadRawMaterials();
            loadDashboardData();
        } else {
            showAlert('Error adding raw material: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error adding raw material:', error);
        showAlert('Error adding raw material: ' + error.message, 'danger');
    });
}

// Open update stock modal
function openUpdateStockModal(materialId, materialName, stockQuantity) {
    document.getElementById('update-material-id').value = materialId;
    document.getElementById('update-material-name').value = materialName;
    document.getElementById('update-stock-quantity').value = stockQuantity;
    
    const updateStockModal = new bootstrap.Modal(document.getElementById('updateStockModal'));
    updateStockModal.show();
}

// Update stock
function updateStock() {
    const materialId = document.getElementById('update-material-id').value;
    const stockQuantity = document.getElementById('update-stock-quantity').value;
    
    // Validate form data
    if (!materialId || !stockQuantity) {
        showAlert('Please fill all required fields', 'warning');
        return;
    }
    
    if (parseInt(stockQuantity) < 0) {
        showAlert('Stock quantity cannot be negative', 'warning');
        return;
    }
    
    const stockData = {
        Material_ID: parseInt(materialId),
        Stock_Quantity: parseInt(stockQuantity)
    };
    
    fetch('../backend/api/update_stock.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(stockData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('updateStockModal'));
            modal.hide();
            
            // Reset form
            document.getElementById('update-stock-form').reset();
            
            // Show success message
            showAlert('Stock updated successfully', 'success');
            
            // Reload raw materials and dashboard data
            loadRawMaterials();
            loadDashboardData();
        } else {
            showAlert('Error updating stock: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error updating stock:', error);
        showAlert('Error updating stock: ' + error.message, 'danger');
    });
}

// Show alert
function showAlert(message, type) {
    // Check if alert container exists, if not create it
    let alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alert-container';
        alertContainer.style.position = 'fixed';
        alertContainer.style.top = '20px';
        alertContainer.style.right = '20px';
        alertContainer.style.zIndex = '9999';
        document.body.appendChild(alertContainer);
    }
    
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add alert to container
    alertContainer.appendChild(alert);
    
    // Auto-dismiss alert after 5 seconds
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => {
            alert.remove();
        }, 150);
    }, 5000);
}
