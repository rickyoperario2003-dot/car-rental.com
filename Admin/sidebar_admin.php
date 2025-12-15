<style>
  .sidebar {
  width: 220px;
  height: 100vh;
  background-color: #2c3e50;
  color: white;
  padding: 20px;
  position: fixed;
}

.sidebar h2 {
  text-align: center;
  margin-bottom: 30px;
}

.sidebar ul {
  list-style-type: none;
  padding: 0;
}

.sidebar ul li {
  margin-bottom: 20px;
}

.sidebar ul li a {
  color: white;
  text-decoration: none;
  display: block;
  padding: 10px;
  border-radius: 4px;
}

.sidebar ul li a:hover {
  background-color: #34495e;
}
</style>
<div class="sidebar">
  <h2>Admin Panel</h2>
  <ul>
    <li><a href="admin_dashboard.php">Dashboard</a></li>
    <li><a href="manage_cars.php">Manage Cars</a></li>
    <li><a href="reservations.php">Reservations</a></li>
    <li><a href="customers.php">Customers</a></li>
    <li><a href="reports.php">Reports</a></li>
    <li><a href="logout.php">Logout</a></li>
  </ul>
</div>
