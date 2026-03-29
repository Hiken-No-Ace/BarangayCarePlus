<?php
session_start();
include("../config/db.php");

# -------------------------
# ADD MONITORING
# -------------------------
if(isset($_POST['add'])){
    $resident_id = $_POST['resident_id'] ?? null;
    $symptoms = $_POST['symptoms'] ?? '';
    $date = $_POST['date'] ?? '';
    $status = $_POST['status'] ?? '';

    if($resident_id && $symptoms && $date && $status){
        $stmt = $conn->prepare("INSERT INTO health_monitoring (resident_id, symptoms, date_recorded, status) VALUES (?,?,?,?)");
        $stmt->bind_param("isss", $resident_id, $symptoms, $date, $status);
        $stmt->execute();
    }
}

# -------------------------
# DELETE MONITORING
# -------------------------
if(isset($_GET['delete'])){
    $stmt = $conn->prepare("DELETE FROM health_monitoring WHERE id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: monitoring.php");
    exit;
}

# -------------------------
# UPDATE MONITORING STATUS
# -------------------------
if(isset($_POST['update'])){
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? '';
    if($id && $status){
        $stmt = $conn->prepare("UPDATE health_monitoring SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        header("Location: monitoring.php");
        exit;
    }
}

# -------------------------
# GET RESIDENTS
# -------------------------
$res = $conn->query("SELECT * FROM residents ORDER BY fullname ASC");

# -------------------------
# GET ALL MONITORING RECORDS
# -------------------------
$mon = $conn->query("SELECT * FROM health_monitoring ORDER BY date_recorded DESC");
?>

<h2>Health Monitoring</h2>

<!-- NAVIGATION -->
<a href="../dashboard/admin.php">← Dashboard</a> |
<a href="residents.php">Residents</a> |
<a href="vitals.php">Vital Signs</a> |
<a href="appointments.php">Appointments</a> |
<a href="vaccinations.php">Vaccinations</a> |
<a href="dental.php">Dental</a> |
<a href="../auth/logout.php">Logout</a>
<hr>

<!-- ADD MONITORING FORM -->
<form method="POST">
<select name="resident_id" required>
<option value="">Select Resident</option>
<?php while($r=$res->fetch_assoc()){ ?>
<option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['fullname']) ?></option>
<?php } ?>
</select><br>
<input name="symptoms" placeholder="Symptoms" required><br>
<input type="date" name="date" required><br>
<select name="status" required>
<option>Under Observation</option>
<option>Recovering</option>
<option>Cleared</option>
</select><br>
<button name="add">Add Monitoring</button>
</form>

<hr>

<!-- MONITORING TABLE -->
<table border="1" cellpadding="5">
<tr>
<th>Resident</th>
<th>Symptoms</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($m=$mon->fetch_assoc()):

    # SAFE: get resident name
    $resident_name = "Unknown";
    if(!empty($m['resident_id'])){
        $r_query = $conn->prepare("SELECT fullname FROM residents WHERE id=?");
        $r_query->bind_param("i", $m['resident_id']);
        $r_query->execute();
        $r_res = $r_query->get_result();
        if($r_res->num_rows > 0){
            $resident_name = $r_res->fetch_assoc()['fullname'];
        }
    }
?>
<tr>
<td><?= htmlspecialchars($resident_name) ?></td>
<td><?= htmlspecialchars($m['symptoms']) ?></td>
<td>
    <!-- EDIT STATUS FORM -->
    <form method="POST" style="display:inline">
        <input type="hidden" name="id" value="<?= $m['id'] ?>">
        <select name="status">
            <option <?= $m['status']=='Under Observation'?'selected':'' ?>>Under Observation</option>
            <option <?= $m['status']=='Recovering'?'selected':'' ?>>Recovering</option>
            <option <?= $m['status']=='Cleared'?'selected':'' ?>>Cleared</option>
        </select>
        <button name="update">Update</button>
    </form>
</td>
<td>
    <a href="?delete=<?= $m['id'] ?>" onclick="return confirm('Delete this record?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>