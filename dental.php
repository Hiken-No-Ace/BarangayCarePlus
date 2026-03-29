<?php
session_start();
include("../config/db.php");

# -------------------------
# ADD DENTAL RECORD
# -------------------------
if(isset($_POST['add'])){
    $resident_id = $_POST['resident_id'] ?? null;
    $checkup_date = $_POST['checkup_date'] ?? '';
    $dental_condition = $_POST['dental_condition'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if($resident_id && $checkup_date && $dental_condition){
        $stmt = $conn->prepare("INSERT INTO dental_records (resident_id, checkup_date, dental_condition, notes) VALUES (?,?,?,?)");
        $stmt->bind_param("isss", $resident_id, $checkup_date, $dental_condition, $notes);
        $stmt->execute();
    }
}

# -------------------------
# DELETE DENTAL RECORD
# -------------------------
if(isset($_GET['delete'])){
    $stmt = $conn->prepare("DELETE FROM dental_records WHERE id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: dental.php");
    exit;
}

# -------------------------
# UPDATE DENTAL RECORD
# -------------------------
if(isset($_POST['update'])){
    $id = $_POST['id'] ?? null;
    $checkup_date = $_POST['checkup_date'] ?? '';
    $dental_condition = $_POST['dental_condition'] ?? '';
    $notes = $_POST['notes'] ?? '';
    if($id && $checkup_date && $dental_condition){
        $stmt = $conn->prepare("UPDATE dental_records SET checkup_date=?, dental_condition=?, notes=? WHERE id=?");
        $stmt->bind_param("sssi", $checkup_date, $dental_condition, $notes, $id);
        $stmt->execute();
        header("Location: dental.php");
        exit;
    }
}

# -------------------------
# GET RESIDENTS
# -------------------------
$res = $conn->query("SELECT * FROM residents ORDER BY fullname ASC");

# -------------------------
# GET ALL DENTAL RECORDS
# -------------------------
$dent = $conn->query("SELECT * FROM dental_records ORDER BY checkup_date DESC");
?>

<h2>Dental Records</h2>

<!-- NAVIGATION -->
<a href="../dashboard/admin.php">← Dashboard</a> |
<a href="residents.php">Residents</a> |
<a href="vitals.php">Vital Signs</a> |
<a href="appointments.php">Appointments</a> |
<a href="vaccinations.php">Vaccinations</a> |
<a href="monitoring.php">Monitoring</a> |
<a href="../auth/logout.php">Logout</a>
<hr>

<!-- ADD DENTAL FORM -->
<form method="POST">
<select name="resident_id" required>
<option value="">Select Resident</option>
<?php while($r = $res->fetch_assoc()){ ?>
<option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['fullname']) ?></option>
<?php } ?>
</select><br>
<input type="date" name="checkup_date" required><br>
<input name="dental_condition" placeholder="Condition (e.g., cavities, gum issues)" required><br>
<input name="notes" placeholder="Notes"><br>
<button name="add">Add Record</button>
</form>

<hr>

<!-- DENTAL TABLE -->
<table border="1" cellpadding="5">
<tr>
<th>Resident</th>
<th>Checkup Date</th>
<th>Condition</th>
<th>Notes</th>
<th>Actions</th>
</tr>

<?php while($d = $dent->fetch_assoc()):

    # SAFE: get resident name
    $resident_name = "Unknown";
    if(!empty($d['resident_id'])){
        $r_query = $conn->prepare("SELECT fullname FROM residents WHERE id=?");
        $r_query->bind_param("i", $d['resident_id']);
        $r_query->execute();
        $r_res = $r_query->get_result();
        if($r_res->num_rows > 0){
            $resident_name = $r_res->fetch_assoc()['fullname'];
        }
    }
?>
<tr>
<td><?= htmlspecialchars($resident_name) ?></td>
<td>
    <!-- EDIT CHECKUP DATE -->
    <form method="POST" style="display:inline">
        <input type="hidden" name="id" value="<?= $d['id'] ?>">
        <input type="date" name="checkup_date" value="<?= $d['checkup_date'] ?>" required>
</td>
<td>
        <input name="dental_condition" value="<?= htmlspecialchars($d['dental_condition']) ?>" required>
</td>
<td>
        <input name="notes" value="<?= htmlspecialchars($d['notes']) ?>">
</td>
<td>
        <button name="update">Update</button>
        <a href="?delete=<?= $d['id'] ?>" onclick="return confirm('Delete this record?')">Delete</a>
    </form>
</td>
</tr>
<?php endwhile; ?>
</table>