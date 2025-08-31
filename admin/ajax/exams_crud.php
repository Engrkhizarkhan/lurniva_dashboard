<?php
require_once '../sass/db_config.php';

$action = $_REQUEST['action'] ?? '';

if($action == "save"){
    $id = $_POST['id'] ?? '';
    $name = $_POST['exam_name'];
    $marks = $_POST['total_marks'];

    if($id){ // Update
        $stmt = $conn->prepare("UPDATE exams SET exam_name=?, total_marks=? WHERE id=?");
        $stmt->bind_param("sii", $name, $marks, $id);
        $ok = $stmt->execute();
        echo json_encode(["status"=>$ok?"success":"error","message"=>$ok?"Updated":"Failed"]);
    } else { // Insert
        $stmt = $conn->prepare("INSERT INTO exams (exam_name,total_marks) VALUES (?,?)");
        $stmt->bind_param("si", $name, $marks);
        $ok = $stmt->execute();
        echo json_encode(["status"=>$ok?"success":"error","message"=>$ok?"Inserted":"Failed"]);
    }
    exit;
}

if($action == "read"){
    $res = $conn->query("SELECT * FROM exams ORDER BY id DESC");
    $rows="";
    while($r=$res->fetch_assoc()){
        $rows.="<tr>
            <td>{$r['id']}</td>
            <td>{$r['exam_name']}</td>
            <td>{$r['total_marks']}</td>
            <td>
                <button class='btn btn-sm btn-info editBtn' data-id='{$r['id']}'>Edit</button>
                <button class='btn btn-sm btn-danger deleteBtn' data-id='{$r['id']}'>Delete</button>
            </td>
        </tr>";
    }
    echo $rows ?: "<tr><td colspan='4'>No exams found</td></tr>";
    exit;
}

if($action == "get"){
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM exams WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if($res){
        echo json_encode(["status"=>"success","data"=>$res]);
    } else {
        echo json_encode(["status"=>"error","message"=>"Not found"]);
    }
    exit;
}

if($action == "delete"){
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM exams WHERE id=?");
    $stmt->bind_param("i",$id);
    $ok = $stmt->execute();
    echo json_encode(["status"=>$ok?"success":"error","message"=>$ok?"Deleted":"Failed"]);
    exit;
}
?>