<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $deleteQuery = "DELETE FROM products WHERE id = $id";
    if (mysqli_query($conn, $deleteQuery)) {
        showMessage("Product deleted successfully", "success");
    } else {
        showMessage("Error deleting product", "danger");
    }
    redirect('admin/products.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)$_POST['category_id'];
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    $imageName = $_POST['existing_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = UPLOAD_PATH;
        $imageName = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
    }

    if ($id > 0) {

        $query = "UPDATE products SET category_id = $category_id, name = '$name', description = '$description', 
                  price = $price, stock = $stock, image = '$imageName', is_featured = $is_featured 
                  WHERE id = $id";
    } else {

        $query = "INSERT INTO products (category_id, name, description, price, stock, image, is_featured) 
                  VALUES ($category_id, '$name', '$description', $price, $stock, '$imageName', $is_featured)";
    }

    if (mysqli_query($conn, $query)) {
        showMessage($id > 0 ? "Product updated successfully" : "Product added successfully", "success");
        redirect('admin/products.php');
    } else {
        showMessage("Error saving product", "danger");
    }
}

$categoriesQuery = "SELECT * FROM categories ORDER BY name";
$categoriesResult = mysqli_query($conn, $categoriesQuery);

$productsQuery = "SELECT p.*, c.name as category_name FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  ORDER BY p.created_at DESC";
$productsResult = mysqli_query($conn, $productsQuery);

$pageTitle = "Manage Products";
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <ul class="admin-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="categories.php">Categories</a></li>
            <li><a href="products.php" class="active">Products</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="customers.php">Customers</a></li>
            <li><a href="reports.php">Revenue Reports</a></li>
            <li><a href="contact-messages.php">Contact Messages</a></li>
        </ul>
    </div>

    <div class="admin-content">
        <h1>Manage Products</h1>

        <?php displayMessage(); ?>

        <button class="btn btn-primary" onclick="openProductModal()">+ Add New Product</button>

        <div class="admin-list-section wide" style="margin-top: 20px;">
            <h2>All Products</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = mysqli_fetch_assoc($productsResult)): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <img src="<?php echo getProductImage($product['image'], $product['name']); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                            class="product-thumb">
                                    </td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td><?php echo formatPrice($product['price']); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td><?php echo $product['is_featured'] ? '⭐' : '-'; ?></td>
                                    <td class="table-actions">
                                        <button onclick="editProduct(<?php echo $product['id']; ?>)" class="btn btn-sm btn-edit">Edit</button>
                                        <a href="products.php?delete=<?php echo $product['id']; ?>"
                                            class="btn btn-sm btn-delete"
                                            onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Product</h2>
            <span class="modal-close" onclick="closeProductModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" enctype="multipart/form-data" id="productForm">
                <input type="hidden" name="id" id="product_id">
                <input type="hidden" name="existing_image" id="existing_image">

                <div class="form-group">
                    <label for="modal_category_id">Category</label>
                    <select id="modal_category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php
                        mysqli_data_seek($categoriesResult, 0);
                        while ($cat = mysqli_fetch_assoc($categoriesResult)):
                        ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="modal_name">Product Name</label>
                    <input type="text" id="modal_name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="modal_description">Description</label>
                    <textarea id="modal_description" name="description" rows="4"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="modal_price">Price (₹)</label>
                        <input type="number" id="modal_price" name="price" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="modal_stock">Stock Quantity</label>
                        <input type="number" id="modal_stock" name="stock" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="modal_image">Product Image</label>
                    <input type="file" id="modal_image" name="image" accept="image/*">
                    <p class="form-text" id="currentImage"></p>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_featured" id="modal_is_featured" value="1">
                        Featured Product
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Product</button>
                    <button type="button" class="btn btn-secondary" onclick="closeProductModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const productModal = document.getElementById('productModal');
const modalTitle = document.getElementById('modalTitle');

function openProductModal() {
    document.getElementById('productForm').reset();
    document.getElementById('product_id').value = '';
    document.getElementById('existing_image').value = '';
    document.getElementById('currentImage').textContent = '';
    modalTitle.textContent = 'Add New Product';
    productModal.style.display = 'block';
}

function closeProductModal() {
    productModal.style.display = 'none';
}

async function editProduct(id) {
    try {
        const response = await fetch(`get_product.php?id=${id}`);
        const product = await response.json();

        document.getElementById('product_id').value = product.id;
        document.getElementById('existing_image').value = product.image;
        document.getElementById('modal_category_id').value = product.category_id;
        document.getElementById('modal_name').value = product.name;
        document.getElementById('modal_description').value = product.description;
        document.getElementById('modal_price').value = product.price;
        document.getElementById('modal_stock').value = product.stock;
        document.getElementById('modal_is_featured').checked = product.is_featured == 1;

        if (product.image) {
            document.getElementById('currentImage').textContent = `Current: ${product.image}`;
        }

        modalTitle.textContent = 'Edit Product';
        productModal.style.display = 'block';
    } catch (error) {
        console.error('Error loading product:', error);
        alert('Error loading product data');
    }
}

window.onclick = function(event) {
    if (event.target == productModal) {
        closeProductModal();
    }
}
</script>

<?php include '../includes/footer.php'; ?>