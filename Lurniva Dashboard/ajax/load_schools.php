<?php
require_once "../sass/db_config.php";

$search = $_GET['search'] ?? "";
$page = $_GET['page'] ?? 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Build query with search
$searchSql = "";
if (!empty($search)) {
    $search = "%$search%";
    $searchSql = "WHERE s.school_name LIKE ? OR s.registration_number LIKE ?";
}

$sql = "
    SELECT s.id, s.school_name, s.city, s.registration_number, s.created_at,
           o.id AS order_id, o.num_students, o.total_price, s.status, p.plan_name, p.duration_days
    FROM schools s
    LEFT JOIN student_plan_orders o ON o.school_id = s.id
    LEFT JOIN student_payment_plans p ON p.id = o.plan_id
    $searchSql
    ORDER BY s.created_at DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $conn->prepare($sql);
if (!empty($searchSql)) {
    $stmt->bind_param("ss", $search, $search);
}
$stmt->execute();
$result = $stmt->get_result();

// Table
echo "<table class='table table-bordered table-striped'>
<thead class='thead-dark'>
<tr>
  <th>ID</th>
  <th>School Name</th>
  <th>Location</th>
  <th>Registration</th>
  <th>Apply Date</th>
  <th>Plan</th>
  <th>No. of Students</th>
  <th>Total Price</th>
  <th>Status</th>
  <th>Action</th>
</tr>
</thead>
<tbody>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['school_name']}</td>
            <td>{$row['city']}</td>
            <td>{$row['registration_number']}</td>
            <td>{$row['created_at']}</td>
            <td>".($row['plan_name'] ? "{$row['plan_name']} ({$row['duration_days']} days)" : "-")."</td>
            <td>{$row['num_students']}</td>
            <td>".($row['total_price'] ? "PKR {$row['total_price']}" : "-")."</td>
            <td>{$row['status']}</td>
            <td><button class='btn btn-success btn-sm' onclick=\"openModal({$row['id']}, '{$row['school_name']}')\">Accept</button></td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='10' class='text-center'>No schools found.</td></tr>";
}
echo "</tbody></table>";

// Pagination
$countSql = "SELECT COUNT(*) AS cnt FROM schools";
if (!empty($searchSql)) {
    $countSql .= " WHERE school_name LIKE ? OR registration_number LIKE ?";
    $stmt2 = $conn->prepare($countSql);
    $stmt2->bind_param("ss", $search, $search);
    $stmt2->execute();
    $countResult = $stmt2->get_result()->fetch_assoc();
} else {
    $countResult = $conn->query($countSql)->fetch_assoc();
}

$totalPages = ceil($countResult['cnt'] / $limit);

echo "<nav><ul class='pagination justify-content-center'>";
for ($i = 1; $i <= $totalPages; $i++) {
    echo "<li class='page-item ".($i==$page?'active':'')."'>
            <a href='#' class='page-link' onclick='loadSchools(\"$search\", $i)'>$i</a>
          </li>";
}
echo "</ul></nav>";