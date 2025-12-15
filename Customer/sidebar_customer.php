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
  <h2>Customer Panel</h2>
  <ul>
    <li><a href="customer_dashboard.php">Dashboard</a></li>
    <li><a href="available_cars.php">Available Cars</a></li>
    <li><a href="my_reservations.php">My Reservations</a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="logout.php">Logout</a></li>
  </ul>
</div>
