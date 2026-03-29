<?php
session_start();
include("../config/db.php");

# -------------------------
# ADD RESIDENT
# -------------------------
if(isset($_POST['add'])){
    $stmt = $conn->prepare("INSERT INTO residents (fullname, age, gender, address, contact, type) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("sissss",
        $_POST['fullname'],
        $_POST['age'],
        $_POST['gender'],
        $_POST['address'],
        $_POST['contact'],
        $_POST['type']
    );
    $stmt->execute();
    header("Location: residents.php"); // Refresh page
    exit;
}

# -------------------------
# DELETE RESIDENT
# -------------------------
if(isset($_GET['delete'])){
    $stmt = $conn->prepare("DELETE FROM residents WHERE id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: residents.php"); // Refresh page
    exit;
}

# -------------------------
# UPDATE RESIDENT
# -------------------------
if(isset($_POST['update'])){
    $stmt = $conn->prepare("UPDATE residents SET fullname=?, age=?, gender=?, address=?, contact=?, type=? WHERE id=?");
    $stmt->bind_param("sissssi",
        $_POST['fullname'],
        $_POST['age'],
        $_POST['gender'],
        $_POST['address'],
        $_POST['contact'],
        $_POST['type'],
        $_POST['id']
    );
    $stmt->execute();
    header("Location: residents.php"); // Refresh page
    exit;
}

$res = $conn->query("SELECT * FROM residents ORDER BY fullname ASC");
?>

<h2>Residents</h2>

<!-- NAVIGATION -->
<a href="../dashboard/admin.php">← Dashboard</a> |
<a href="vitals.php">Vital Signs</a> |
<a href="appointments.php">Appointments</a> |
<a href="vaccinations.php">Vaccinations</a> |
<a href="dental.php">Dental</a> |
<a href="monitoring.php">Monitoring</a> |
<a href="../auth/logout.php">Logout</a>
<hr>

<!-- ADD RESIDENT FORM -->
<h3>Add Resident</h3>
<form method="POST">
<input name="fullname" placeholder="Full Name" required><br>
<input name="age" placeholder="Age" type="number" required><br>
<input name="gender" placeholder="Gender" required><br>
<input name="address" placeholder="Address" required><br>
<input name="contact" placeholder="Contact Number" required><br>
<select name="type">
<option>Adult</option>
<option>Child</option>
</select><br>
<button name="add">Add Resident</button>
</form>

<hr>

<!-- RESIDENTS TABLE -->
<table border="1" cellpadding="5">
<tr>
<th>Name</th>
<th>Age</th>
<th>Gender</th>
<th>Address</th>
<th>Contact Number</th>
<th>Type</th>
<th>Actions</th>
</tr>

<?php while($row = $res->fetch_assoc()){ ?>
<tr>
<form method="POST">
<input type="hidden" name="id" value="<?= $row['id'] ?>">
<td><input name="fullname" value="<?= htmlspecialchars($row['fullname']) ?>" required></td>
<td><input name="age" type="number" value="<?= $row['age'] ?>" required></td>
<td><input name="gender" value="<?= htmlspecialchars($row['gender']) ?>" required></td>
<td><input name="address" value="<?= htmlspecialchars($row['address']) ?>" required></td>
<td><input name="contact" value="<?= htmlspecialchars($row['contact']) ?>" required></td>
<td>
<select name="type">
<option <?= $row['type'] == 'Adult' ? 'selected' : '' ?>>Adult</option>
<option <?= $row['type'] == 'Child' ? 'selected' : '' ?>>Child</option>
</select>
</td>
<td>
<button name="update">Update</button>
<a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this resident?')">Delete</a>
</td>
</form>
</tr>
<?php } ?>
</table>