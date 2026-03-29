<?php
session_start();
include("../config/db.php");

# -------------------------
# ADD VITAL SIGN
# -------------------------
if(isset($_POST['add'])){
    $resident_id = $_POST['resident_id'] ?? null;
    $temperature = $_POST['temperature'] ?? '';
    $blood_pressure = $_POST['blood_pressure'] ?? '';
    $heart_rate = $_POST['heart_rate'] ?? '';
    $date_recorded = $_POST['date_recorded'] ?? '';

    if($resident_id && $temperature && $blood_pressure && $heart_rate && $date_recorded){
        $stmt = $conn->prepare("INSERT INTO vital_signs (resident_id, temperature, blood_pressure, heart_rate, date_recorded) VALUES (?,?,?,?,?)");
        $stmt->bind_param("issss", $resident_id, $temperature, $blood_pressure, $heart_rate, $date_recorded);
        $stmt->execute();
    }
}

# -------------------------
# DELETE VITAL SIGN
# -------------------------
if(isset($_GET['delete'])){
    $stmt = $conn->prepare("DELETE FROM vital_signs WHERE id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: vitals.php");
    exit;
}

# -------------------------
# UPDATE VITAL SIGN
# -------------------------
if(isset($_POST['update'])){
    $id = $_POST['id'] ?? null;
    $temperature = $_POST['temperature'] ?? '';
    $blood_pressure = $_POST['blood_pressure'] ?? '';
    $heart_rate = $_POST['heart_rate'] ?? '';
    $date_recorded = $_POST['date_recorded'] ?? '';

    if($id && $temperature && $blood_pressure && $heart_rate && $date_recorded){
        $stmt = $conn->prepare("UPDATE vital_signs SET temperature=?, blood_pressure=?, heart_rate=?, date_recorded=? WHERE id=?");
        $stmt->bind_param("ssssi", $temperature, $blood_pressure, $heart_rate, $date_recorded, $id);
        $stmt->execute();
        header("Location: vitals.php");
        exit;
    }
}

# -------------------------
# GET RESIDENTS
# -------------------------
$res = $conn->query("SELECT * FROM residents ORDER BY fullname ASC");

# -------------------------
# GET ALL VITAL SIGNS
# -------------------------
$vitals = $conn->query("SELECT * FROM vital_signs ORDER BY date_recorded DESC");
?>

<h2>Vital Signs</h2>

<!-- NAVIGATION -->
<a href="../dashboard/admin.php">← Dashboard</a> |
<a href="residents.php">Residents</a> |
<a href="appointments.php">Appointments</a> |
<a href="vaccinations.php">Vaccinations</a> |
<a href="dental.php">Dental</a> |
<a href="monitoring.php">Monitoring</a> |
<a href="../auth/logout.php">Logout</a>
<hr>

<!-- ADD VITAL SIGN FORM -->
<form method="POST">
<select name="resident_id" required>
<option value="">Select Resident</option>
<?php while($r = $res->fetch_assoc()){ ?>
<option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['fullname']) ?></option>
<?php } ?>
</select><br>
<input name="temperature" placeholder="Temperature" required><br>
<input name="blood_pressure" placeholder="Blood Pressure" required><br>
<input name="heart_rate" placeholder="Heart Rate" required><br>
<input type="date" name="date_recorded" required><br>
<button name="add">Add Vital Sign</button>
</form>

<hr>

<!-- VITAL SIGNS TABLE -->
<table border="1" cellpadding="5">
<tr>
<th>Resident</th>
<th>Temperature</th>
<th>Blood Pressure</th>
<th>Heart Rate</th>
<th>Date</th>
<th>Actions</th>
</tr>

<?php while($v = $vitals->fetch_assoc()):

    # SAFE: get resident name
    $resident_name = "Unknown";
    if(!empty($v['resident_id'])){
        $r_query = $conn->prepare("SELECT fullname FROM residents WHERE id=?");
        $r_query->bind_param("i", $v['resident_id']);
        $r_query->execute();
        $r_res = $r_query->get_result();
        if($r_res->num_rows > 0){
            $resident_name = $r_res->fetch_assoc()['fullname'];
        }
    }
?>
<tr>
<td><?= htmlspecialchars($resident_name) ?></td>

<!-- EDIT FORM -->
<form method="POST">
<input type="hidden" name="id" value="<?= $v['id'] ?>">
<td><input name="temperature" value="<?= htmlspecialchars($v['temperature']) ?>" required></td>
<td><input name="blood_pressure" value="<?= htmlspecialchars($v['blood_pressure']) ?>" required></td>
<td><input name="heart_rate" value="<?= htmlspecialchars($v['heart_rate']) ?>" required></td>
<td><input type="date" name="date_recorded" value="<?= $v['date_recorded'] ?>" required></td>
<td>
<button name="update">Update</button>
<a href="?delete=<?= $v['id'] ?>" onclick="return confirm('Delete this record?')">Delete</a>
</td>
</form>
</tr>
<?php endwhile; ?>
</table>