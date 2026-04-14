<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$revenueQuery = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value,
        SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as completed_revenue,
        SUM(CASE WHEN status = 'processing' THEN total_amount ELSE 0 END) as processing_revenue,
        SUM(CASE WHEN status = 'pending' THEN total_amount ELSE 0 END) as pending_revenue,
        SUM(CASE WHEN payment_method = 'COD' THEN total_amount ELSE 0 END) as cod_revenue,
        SUM(CASE WHEN payment_method = 'Card' THEN total_amount ELSE 0 END) as card_revenue,
        SUM(CASE WHEN payment_method = 'UPI' THEN total_amount ELSE 0 END) as upi_revenue,
        SUM(CASE WHEN payment_method = 'Wallet' THEN total_amount ELSE 0 END) as wallet_revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
";
$revenueResult = mysqli_query($conn, $revenueQuery);
$revenue = mysqli_fetch_assoc($revenueResult);

$dailyRevenueQuery = "
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as orders,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
";
$dailyRevenueResult = mysqli_query($conn, $dailyRevenueQuery);

$topProductsQuery = "
    SELECT 
        p.name,
        p.price,
        SUM(oi.quantity) as total_sold,
        SUM(oi.subtotal) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'
    GROUP BY oi.product_id
    ORDER BY total_revenue DESC
    LIMIT 10
";
$topProductsResult = mysqli_query($conn, $topProductsQuery);

$paymentMethodQuery = "
    SELECT 
        COALESCE(payment_method, 'COD') as method,
        COUNT(*) as order_count,
        SUM(total_amount) as revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    GROUP BY payment_method
";
$paymentMethodResult = mysqli_query($conn, $paymentMethodQuery);

$customerQuery = "
    SELECT 
        u.name,
        u.email,
        COUNT(o.id) as order_count,
        SUM(o.total_amount) as total_spent
    FROM users u
    JOIN orders o ON u.id = o.user_id
    WHERE DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'
    GROUP BY u.id
    ORDER BY total_spent DESC
    LIMIT 10
";
$customerResult = mysqli_query($conn, $customerQuery);

$pageTitle = "Revenue Reports";
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <ul class="admin-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="categories.php">Categories</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="customers.php">Customers</a></li>
            <li><a href="reports.php" class="active">Revenue Reports</a></li>
            <li><a href="contact-messages.php">Contact Messages</a></li>
        </ul>
    </div>

    <div class="admin-content" style="padding-right: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1><i class="fas fa-chart-line"></i> Revenue Reports & Analytics</h1>
            <button onclick="exportToExcel()" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export to Excel
            </button>
        </div>

        <!-- Date Filter -->
        <div class="filter-section">
            <form method="GET" class="date-filter-form">
                <div class="form-group">
                    <label>Start Date:</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" required>
                </div>
                <div class="form-group">
                    <label>End Date:</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="reports.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </form>
        </div>

        <!-- Revenue Stats Grid -->
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin: 2rem 0;">
            <div class="stat-card" style="background: #667eea; color: white;">
                <h3 style="color: white;">Total Revenue</h3>
                <p class="stat-number" style="color: white;"><?php echo formatPrice($revenue['total_revenue'] ?? 0); ?></p>
                <small style="color: white;"><?php echo $revenue['total_orders']; ?> orders</small>
            </div>
            <div class="stat-card" style="background: #e74c3c; color: white;">
                <h3 style="color: white;">Avg Order Value</h3>
                <p class="stat-number" style="color: white;"><?php echo formatPrice($revenue['avg_order_value'] ?? 0); ?></p>
                <small style="color: white;">Per transaction</small>
            </div>
            <div class="stat-card" style="background: #3498db; color: white;">
                <h3 style="color: white;">Completed Orders</h3>
                <p class="stat-number" style="color: white;"><?php echo formatPrice($revenue['completed_revenue'] ?? 0); ?></p>
                <small style="color: white;">Revenue received</small>
            </div>
            <div class="stat-card" style="background: #27ae60; color: white;">
                <h3 style="color: white;">Processing Orders</h3>
                <p class="stat-number" style="color: white;"><?php echo formatPrice($revenue['processing_revenue'] ?? 0); ?></p>
                <small style="color: white;">In progress</small>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-grid">
            <!-- Daily Revenue Chart -->
            <div class="chart-card">
                <h3><i class="fas fa-chart-area"></i> Daily Revenue Trend</h3>
                <div style="position: relative; height: 300px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Payment Method Chart -->
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Payment Methods</h3>
                <div style="position: relative; height: 300px;">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Payment Method Breakdown -->
        <div class="report-section">
            <h3><i class="fas fa-credit-card"></i> Payment Method Breakdown</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th>Orders</th>
                        <th>Revenue</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($paymentMethodResult, 0);
                    while ($method = mysqli_fetch_assoc($paymentMethodResult)): 
                        $percentage = ($revenue['total_revenue'] > 0) ? ($method['revenue'] / $revenue['total_revenue'] * 100) : 0;
                    ?>
                        <tr>
                            <td>
                                <i class="fas fa-<?php 
                                    echo $method['method'] == 'Card' ? 'credit-card' : 
                                        ($method['method'] == 'UPI' ? 'mobile-alt' : 
                                        ($method['method'] == 'Wallet' ? 'wallet' : 'money-bill-wave')); 
                                ?>"></i>
                                <?php echo htmlspecialchars($method['method']); ?>
                            </td>
                            <td><?php echo $method['order_count']; ?></td>
                            <td><?php echo formatPrice($method['revenue']); ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    <span><?php echo number_format($percentage, 1); ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Top Selling Products -->
        <div class="report-section">
            <h3><i class="fas fa-trophy"></i> Top Selling Products</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Product Name</th>
                        <th>Unit Price</th>
                        <th>Units Sold</th>
                        <th>Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    while ($product = mysqli_fetch_assoc($topProductsResult)): 
                    ?>
                        <tr>
                            <td><strong><?php echo $rank++; ?></strong></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td><?php echo $product['total_sold']; ?></td>
                            <td><strong><?php echo formatPrice($product['total_revenue']); ?></strong></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Top Customers -->
        <div class="report-section">
            <h3><i class="fas fa-users"></i> Top Customers</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Total Orders</th>
                        <th>Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    while ($customer = mysqli_fetch_assoc($customerResult)): 
                    ?>
                        <tr>
                            <td><strong><?php echo $rank++; ?></strong></td>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo $customer['order_count']; ?></td>
                            <td><strong><?php echo formatPrice($customer['total_spent']); ?></strong></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<!-- SheetJS for Excel Export -->
<script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>

<style>
.admin-content {
    max-width: 100%;
    overflow-x: hidden;
}

.filter-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.date-filter-form {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    flex-wrap: wrap;
}

.date-filter-form .form-group {
    flex: 1;
    min-width: 150px;
    margin: 0;
}

.date-filter-form .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.date-filter-form input[type="date"] {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.charts-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 1.5rem;
    margin: 2rem 0;
}

.chart-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    min-width: 0;
    overflow: hidden;
}

.chart-card h3 {
    margin-bottom: 1.5rem;
    color: #2c3e50;
}

@media (max-width: 1024px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    .stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr !important;
    }
    .date-filter-form {
        flex-direction: column;
    }
    .admin-container {
        flex-direction: column;
    }
}

.report-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    margin: 2rem 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow-x: auto;
}

.report-section h3 {
    margin-bottom: 1.5rem;
    color: #2c3e50;
    border-bottom: 2px solid #667eea;
    padding-bottom: 0.5rem;
}

.report-section table {
    min-width: 600px;
}

.progress-bar {
    position: relative;
    height: 24px;
    background: #f0f0f0;
    border-radius: 12px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.3s ease;
}

.progress-bar span {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.85rem;
}

.stat-card small {
    opacity: 0.9;
    font-size: 0.85rem;
}
</style>

<script>

<?php
mysqli_data_seek($dailyRevenueResult, 0);
$dates = [];
$revenues = [];
while ($row = mysqli_fetch_assoc($dailyRevenueResult)) {
    $dates[] = date('M d', strtotime($row['date']));
    $revenues[] = $row['revenue'];
}

mysqli_data_seek($paymentMethodResult, 0);
$paymentMethods = [];
$paymentRevenues = [];
while ($row = mysqli_fetch_assoc($paymentMethodResult)) {
    $paymentMethods[] = $row['method'];
    $paymentRevenues[] = $row['revenue'];
}
?>

const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode($revenues); ?>,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Revenue: ₹' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    }
                }
            },
            x: {
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            }
        }
    }
});

const paymentCtx = document.getElementById('paymentChart').getContext('2d');
const paymentChart = new Chart(paymentCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($paymentMethods); ?>,
        datasets: [{
            data: <?php echo json_encode($paymentRevenues); ?>,
            backgroundColor: [
                '#667eea',
                '#f093fb',
                '#4facfe',
                '#43e97b'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 11
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': ₹' + context.parsed.toLocaleString();
                    }
                }
            }
        }
    }
});

function exportToExcel() {

    const wb = XLSX.utils.book_new();

    const summaryData = [
        ['Revenue Report - The Shoe Vault'],
        ['Period: <?php echo $start_date; ?> to <?php echo $end_date; ?>'],
        [''],
        ['Metric', 'Value'],
        ['Total Orders', '<?php echo $revenue['total_orders']; ?>'],
        ['Total Revenue', '<?php echo $revenue['total_revenue']; ?>'],
        ['Average Order Value', '<?php echo $revenue['avg_order_value']; ?>'],
        ['Completed Revenue', '<?php echo $revenue['completed_revenue']; ?>'],
        ['Processing Revenue', '<?php echo $revenue['processing_revenue']; ?>'],
        [''],
        ['Payment Method Breakdown'],
        ['Method', 'Orders', 'Revenue'],
        <?php
        mysqli_data_seek($paymentMethodResult, 0);
        while ($method = mysqli_fetch_assoc($paymentMethodResult)) {
            echo "['".addslashes($method['method'])."', '".$method['order_count']."', '".$method['revenue']."'],\n";
        }
        ?>
    ];
    const ws1 = XLSX.utils.aoa_to_sheet(summaryData);
    XLSX.utils.book_append_sheet(wb, ws1, 'Summary');

    const productData = [
        ['Top Selling Products'],
        ['Rank', 'Product Name', 'Unit Price', 'Units Sold', 'Total Revenue'],
        <?php
        mysqli_data_seek($topProductsResult, 0);
        $rank = 1;
        while ($product = mysqli_fetch_assoc($topProductsResult)) {
            echo "[".$rank++.", '".addslashes($product['name'])."', '".$product['price']."', '".$product['total_sold']."', '".$product['total_revenue']."'],\n";
        }
        ?>
    ];
    const ws2 = XLSX.utils.aoa_to_sheet(productData);
    XLSX.utils.book_append_sheet(wb, ws2, 'Top Products');

    const customerData = [
        ['Top Customers'],
        ['Rank', 'Name', 'Email', 'Total Orders', 'Total Spent'],
        <?php
        mysqli_data_seek($customerResult, 0);
        $rank = 1;
        while ($customer = mysqli_fetch_assoc($customerResult)) {
            echo "[".$rank++.", '".addslashes($customer['name'])."', '".addslashes($customer['email'])."', '".$customer['order_count']."', '".$customer['total_spent']."'],\n";
        }
        ?>
    ];
    const ws3 = XLSX.utils.aoa_to_sheet(customerData);
    XLSX.utils.book_append_sheet(wb, ws3, 'Top Customers');

    const filename = 'Revenue_Report_<?php echo $start_date; ?>_to_<?php echo $end_date; ?>.xlsx';
    XLSX.writeFile(wb, filename);
}
</script>

<?php include '../includes/footer.php'; ?>
