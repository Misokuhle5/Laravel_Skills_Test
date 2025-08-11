<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventory App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div class="container mt-4">
    <h2>Product Inventory</h2>
    
    <!-- Form for adding products -->
    <form id="addProductForm" class="mb-3">
        <div class="row">
            <div class="col-md-4 mb-2">
                <label for="productName" class="form-label">Product</label>
                <input type="text" class="form-control" id="productName" name="product_name" required>
            </div>
            <div class="col-md-2 mb-2">
                <label for="qty" class="form-label">Stock</label>
                <input type="number" class="form-control" id="qty" name="quantity" min="0" required>
            </div>
            <div class="col-md-2 mb-2">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" min="0" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success mt-4">Add</button>
            </div>
        </div>
    </form>

    <!-- Table to show products -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Added On</th>
                <th>Total</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody id="productsBody"></tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end"><b>Grand Total:</b></td>
                <td id="grandTotal">0.00</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Modal for editing -->
<div class="modal" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateForm">
                    <input type="hidden" id="editId" name="id">
                    <div class="mb-3">
                        <label for="editProduct" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="editProduct" name="product_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editQty" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="editQty" name="quantity" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPrice" class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control" id="editPrice" name="price" min="0" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

// Setup CSRF for Ajax
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Load products from server
function loadProducts() {
    console.log('Fetching products...'); // Debug
    $.get('/api/products', function(data) {
        let tableBody = '';
        let total = 0;
        for (let i = 0; i < data.length; i++) {
            let prod = data[i];
            let prodTotal = (prod.quantity * prod.price).toFixed(2);
            total += parseFloat(prodTotal);
            tableBody += '<tr>' +
                '<td>' + prod.product_name + '</td>' +
                '<td>' + prod.quantity + '</td>' +
                '<td>' + prod.price + '</td>' +
                '<td>' + new Date(prod.submitted_at).toLocaleString() + '</td>' +
                '<td>' + prodTotal + '</td>' +
                '<td><button class="btn btn-sm btn-info editProd" data-id="' + prod.id + '" data-name="' + prod.product_name + '" data-qty="' + prod.quantity + '" data-price="' + prod.price + '">Edit</button></td>' +
                '</tr>';
        }
        $('#productsBody').html(tableBody);
        $('#grandTotal').text(grandTotal.toFixed(2));
        console.log('Total calculated: ' + grandTotal); // Debug
    }).fail(function() {
        console.log('Error loading products');
    });
}

loadProducts();

// Handle form submission
$('#addProductForm').on('submit', function(e) {
    e.preventDefault();
    console.log('Adding product...'); // Debug
    let formData = $(this).serialize();
    $.post('/api/products', formData, function(response) {
        if (response.status === 'ok') {
            $('#addProductForm')[0].reset();
            loadProducts();
        }
    }).fail(function() {
        console.log('Failed to add product');
    });
});

// Handle edit button click
$(document).on('click', '.editProd', function() {
    $('#editId').val($(this).data('id'));
    $('#editProduct').val($(this).data('name'));
    $('#editQty').val($(this).data('qty'));
    $('#editPrice').val($(this).data('price'));
    $('#editProductModal').modal('show');
});

// Handle edit form submission
$('#updateForm').on('submit', function(e) {
    e.preventDefault();
    let id = $('#editId').val();
    console.log('Updating product ID: ' + id); // Debug
    $.ajax({
        url: '/api/products/' + id,
        type: 'PUT',
        data: $(this).serialize(),
        success: function(response) {
            if (response.status === 'ok') {
                $('#editProductModal').modal('hide');
                loadProducts();
            }
        },
        error: function() {
            console.log('Failed to update product');
        }
    });
});
</script>
</body>
</html>