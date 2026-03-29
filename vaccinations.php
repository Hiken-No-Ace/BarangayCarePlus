<?php
session_start();
include("../config/db.php");

# -------------------------
# ADD VACCINATION
# -------------------------
if(isset($_POST['add'])){
    $resident_id = $_POST['resident_id'] ?? null;
    $vaccine_name = $_POST['vaccine_name'] ?? '';
    $date_given = $_POST['date_given'] ?? '';

    if($resident_id && $vaccine_name && $date_given){
        $stmt = $conn->prepare("INSERT INTO vaccinations (resident_id, vaccine_name, date_given) VALUES (?,?,?)");
        $stmt->bind_param("iss", $resident_id, $vaccine_name, $date_given);
        $stmt->execute();
        header("Location: vaccinations.php");
        exit;
    }
}

# -------------------------
# DELETE VACCINATION
# -------------------------
if(isset($_GET['delete'])){
    $stmt = $conn->prepare("DELETE FROM vaccinations WHERE id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: vaccinations.php");
    exit;
}

# -------------------------
# UPDATE VACCINATION
# -------------------------
if(isset($_POST['update'])){
    $id = $_POST['id'] ?? null;
    $vaccine_name = $_POST['vaccine_name'] ?? '';
    $date_given = $_POST['date_given'] ?? '';

    if($id && $vaccine_name && $date_given){
        $stmt = $conn->prepare("UPDATE vaccinations SET vaccine_name=?, date_given=? WHERE id=?");
        $stmt->bind_param("ssi", $vaccine_name, $date_given, $id);
        $stmt->execute();
        header("Location: vaccinations.php");
        exit;
    }
}

# -------------------------
# GET CHILD RESIDENTS ONLY
# -------------------------
$res = $conn->query("SELECT * FROM residents WHERE type='Child' ORDER BY fullname ASC");

# -------------------------
# GET ALL VACCINATIONS
# -------------------------
$vacc = $conn->query("SELECT * FROM vaccinations ORDER BY date_given DESC");
?>

<h2>Vaccinations</h2>

<!-- NAVIGATION -->
<a href="../dashboard/admin.php">← Dashboard</a> |
<a href="residents.php">Residents</a> |
<a href="vitals.php">Vital Signs</a> |
<a href="appointments.php">Appointments</a> |
<a href="dental.php">Dental</a> |
<a href="monitoring.php">Monitoring</a> |
<a href="../auth/logout.php">Logout</a>
<hr>

<!-- ADD VACCINATION FORM -->
<h3>Add Vaccination</h3>
<form method="POST">
<select name="resident_id" required>
<option value="">Select Child</option>
<?php while($r = $res->fetch_assoc()){ ?>
<option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['fullname']) ?></option>
<?php } ?>
</select><br>
<input name="vaccine_name" placeholder="Vaccine Name" required><br>
<input type="date" name="date_given" required><br>
<button name="add">Add Vaccine</button>
</form>

<hr>

<!-- VACCINATION TABLE -->
<table border="1" cellpadding="5">
<tr>
<th>Resident</th>
<th>Vaccine</th>
<th>Date</th>
<th>Actions</th>
</tr>

<?php while($v = $vacc->fetch_assoc()):

    # GET RESIDENT NAME SAFELY
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
<form method="POST">
<input type="hidden" name="id" value="<?= $v['id'] ?>">

<td><?= htmlspecialchars($resident_name) ?></td>

<td>
<input name="vaccine_name" value="<?= htmlspecialchars($v['vaccine_name']) ?>" required>
</td>

<td>
<input type="date" name="date_given" value="<?= $v['date_given'] ?>" required>
</td>

<td>
<button name="update">Update</button>
<a href="?delete=<?= $v['id'] ?>" onclick="return confirm('Delete this record?')">Delete</a>
</td>

</form>
</tr>

<?php endwhile; ?>
</table>