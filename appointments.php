<?php
session_start();
include("../config/db.php");

# -------------------------
# ADD APPOINTMENT
# -------------------------
if(isset($_POST['add'])){
    $resident_id = $_POST['resident_id'] ?? null;
    $datetime = $_POST['datetime'] ?? '';
    $purpose = $_POST['purpose'] ?? '';
    $status = $_POST['status'] ?? '';

    if($resident_id && $datetime && $purpose && $status){
        $stmt = $conn->prepare("INSERT INTO appointments (resident_id, datetime, purpose, status) VALUES (?,?,?,?)");
        $stmt->bind_param("isss", $resident_id, $datetime, $purpose, $status);
        $stmt->execute();
    }
}

# -------------------------
# DELETE APPOINTMENT
# -------------------------
if(isset($_GET['delete'])){
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: appointments.php");
    exit;
}

# -------------------------
# UPDATE APPOINTMENT STATUS
# -------------------------
if(isset($_POST['update'])){
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? '';
    if($id && $status){
        $stmt = $conn->prepare("UPDATE appointments SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        header("Location: appointments.php");
        exit;
    }
}

# -------------------------
# GET RESIDENTS
# -------------------------
$res = $conn->query("SELECT * FROM residents ORDER BY fullname ASC");

# -------------------------
# GET ALL APPOINTMENTS
# -------------------------
$app = $conn->query("SELECT * FROM appointments ORDER BY datetime DESC");
?>

<h2>Appointments</h2>

<!-- NAVIGATION -->
<a href="../dashboard/admin.php">← Dashboard</a> |
<a href="residents.php">Residents</a> |
<a href="vitals.php">Vital Signs</a> |
<a href="vaccinations.php">Vaccinations</a> |
<a href="dental.php">Dental</a> |
<a href="monitoring.php">Monitoring</a> |
<a href="../auth/logout.php">Logout</a>
<hr>

<!-- ADD APPOINTMENT FORM -->
<form method="POST">
<select name="resident_id" required>
<option value="">Select Resident</option>
<?php while($r = $res->fetch_assoc()){ ?>
<option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['fullname']) ?></option>
<?php } ?>
</select><br>
<input type="datetime-local" name="datetime" required><br>
<input name="purpose" placeholder="Purpose" required><br>
<select name="status" required>
<option>Pending</option>
<option>Completed</option>
<option>Cancelled</option>
</select><br>
<button name="add">Add Appointment</button>
</form>

<hr>

<!-- APPOINTMENTS TABLE -->
<table border="1" cellpadding="5">
<tr>
<th>Resident</th>
<th>Purpose</th>
<th>Status</th>
<th>Date/Time</th>
<th>Actions</th>
</tr>

<?php while($a = $app->fetch_assoc()):

    # SAFE: get resident name
    $resident_name = "Unknown";
    if(!empty($a['resident_id'])){
        $r_query = $conn->prepare("SELECT fullname FROM residents WHERE id=?");
        $r_query->bind_param("i", $a['resident_id']);
        $r_query->execute();
        $r_res = $r_query->get_result();
        if($r_res->num_rows > 0){
            $resident_name = $r_res->fetch_assoc()['fullname'];
        }
    }
?>
<tr>
<td><?= htmlspecialchars($resident_name) ?></td>
<td><?= htmlspecialchars($a['purpose']) ?></td>
<td>
    <!-- EDIT STATUS FORM -->
    <form method="POST" style="display:inline">
        <input type="hidden" name="id" value="<?= $a['id'] ?>">
        <select name="status">
            <option <?= $a['status']=='Pending'?'selected':'' ?>>Pending</option>
            <option <?= $a['status']=='Completed'?'selected':'' ?>>Completed</option>
            <option <?= $a['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
        </select>
        <button name="update">Update</button>
    </form>
</td>
<td><?= htmlspecialchars($a['datetime']) ?></td>
<td>
    <a href="?delete=<?= $a['id'] ?>" onclick="return confirm('Delete this appointment?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>