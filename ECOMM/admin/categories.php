<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $deleteQuery = "DELETE FROM categories WHERE id = $id";
    if (mysqli_query($conn, $deleteQuery)) {
        showMessage("Category deleted successfully", "success");
    } else {
        showMessage("Error deleting category", "danger");
    }
    redirect('admin/categories.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    $imageName = $_POST['existing_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = UPLOAD_PATH;
        $imageName = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
    }

    if ($id > 0) {

        $query = "UPDATE categories SET name = '$name', description = '$description', image = '$imageName' WHERE id = $id";
    } else {

        $query = "INSERT INTO categories (name, description, image) VALUES ('$name', '$description', '$imageName')";
    }

    if (mysqli_query($conn, $query)) {
        showMessage($id > 0 ? "Category updated successfully" : "Category added successfully", "success");
        redirect('admin/categories.php');
    } else {
        showMessage("Error saving category", "danger");
    }
}

$categoriesQuery = "SELECT * FROM categories ORDER BY name";
$categoriesResult = mysqli_query($conn, $categoriesQuery);

$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editQuery = "SELECT * FROM categories WHERE id = $editId";
    $editResult = mysqli_query($conn, $editQuery);
    $editCategory = mysqli_fetch_assoc($editResult);
}

$pageTitle = "Manage Categories";
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <ul class="admin-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="categories.php" class="active">Categories</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="customers.php">Customers</a></li>
            <li><a href="reports.php">Revenue Reports</a></li>
            <li><a href="contact-messages.php">Contact Messages</a></li>
        </ul>
    </div>

    <div class="admin-content">
        <h1>Manage Categories</h1>

        <?php displayMessage(); ?>

        <div class="admin-grid">
            <div class="admin-form-section">
                <h2><?php echo $editCategory ? 'Edit Category' : 'Add New Category'; ?></h2>
                <form method="POST" enctype="multipart/form-data" id="categoryForm">
                    <?php if ($editCategory): ?>
                        <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                        <input type="hidden" name="existing_image" value="<?php echo $editCategory['image']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="name">Category Name</label>
                        <input type="text" id="name" name="name" required
                            value="<?php echo $editCategory ? htmlspecialchars($editCategory['name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3"><?php echo $editCategory ? htmlspecialchars($editCategory['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Category Image</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <?php if ($editCategory && $editCategory['image']): ?>
                            <p class="form-text">Current: <?php echo htmlspecialchars($editCategory['image']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $editCategory ? 'Update Category' : 'Add Category'; ?>
                        </button>
                        <?php if ($editCategory): ?>
                            <a href="categories.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="admin-list-section">
                <h2>All Categories</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($category = mysqli_fetch_assoc($categoriesResult)): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($category['description'], 0, 50)) . '...'; ?></td>
                                    <td class="table-actions">
                                        <a href="categories.php?edit=<?php echo $category['id']; ?>" class="btn btn-sm btn-edit">Edit</a>
                                        <a href="categories.php?delete=<?php echo $category['id']; ?>"
                                            class="btn btn-sm btn-delete"
                                            onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
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

<?php include '../includes/footer.php'; ?>