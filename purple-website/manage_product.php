<?php
// Database connection
$conn = new mysqli('localhost', 'root', 'Dsp1234@', 'login_register');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];
    $newImage = $_FILES['product_image'];

    if (!empty($newImage['name'])) {
        // If a new image is uploaded, update it
        $imageName = time() . '_' . basename($newImage['name']);
        $targetDir = 'uploads/';
        $targetFile = $targetDir . $imageName;
        move_uploaded_file($newImage['tmp_name'], $targetFile);

        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, image = ? WHERE id = ?");
        $stmt->bind_param("sssi", $productName, $productPrice, $imageName, $productId);
    } else {
        // Update without changing the image
        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ? WHERE id = ?");
        $stmt->bind_param("ssi", $productName, $productPrice, $productId);
    }

    $stmt->execute();
    $stmt->close();
    header("Location: manage_product.php");
    exit();
}

// Fetch all products
$sql = "SELECT id, image, name, price FROM products"; // Fetch only relevant columns
$result = $conn->query($sql);

// Fetch a product for editing
$editingProduct = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $conn->prepare("SELECT id, image, name, price FROM products WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editResult = $stmt->get_result();
    $editingProduct = $editResult->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .table {
            margin-top: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
        .btn-add-product, .btn-dashboard {
            margin-bottom: 20px;
            color: white;
        }
        .btn-add-product {
            background-color: #28a745;
        }
        .btn-dashboard {
            background-color: #007bff;
        }
        .form-edit {
            margin-top: 30px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Manage Products</h1>
        <a href="admin_dashboard.php" class="btn btn-dashboard">Go to Admin Dashboard</a>
        <a href="add_product.php" class="btn btn-add-product">Add New Product</a>

        <!-- Product List in Table -->
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td>
                        <img src="uploads/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                    </td>
                    <td><?php echo $row['name']; ?></td>
                    <td>$<?php echo $row['price']; ?></td>
                    <td>
                        <a href="manage_products.php?edit=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <form action="manage_products.php" method="post" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_product" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Edit Product Form -->
        <?php if ($editingProduct) { ?>
        <div class="form-edit">
            <h2>Edit Product</h2>
            <form action="manage_product.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo $editingProduct['id']; ?>">

                <div class="mb-3">
                    <label for="product_name" class="form-label">Product Name</label>
                    <input type="text" name="product_name" id="product_name" class="form-control" value="<?php echo $editingProduct['name']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="product_price" class="form-label">Product Price</label>
                    <input type="number" step="0.01" name="product_price" id="product_price" class="form-control" value="<?php echo $editingProduct['price']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="product_image" class="form-label">Product Image</label>
                    <input type="file" name="product_image" id="product_image" class="form-control">
                    <small>Leave blank to keep the current image.</small>
                </div>

                <button type="submit" name="update_product" class="btn btn-success">Update Product</button>
            </form>
        </div>
        <?php } ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
