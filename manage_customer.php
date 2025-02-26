<?php
include("config.php");
session_start();
$session_first_name = $_SESSION['first_name'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_customer'])) {
    $customer_id = $mysqli->real_escape_string($_POST['customer_id']);
    $updated_first_name = $mysqli->real_escape_string($_POST['first_name']);
    $updated_last_name = $mysqli->real_escape_string($_POST['last_name']);
    $updated_email = $mysqli->real_escape_string($_POST['email']);
    $updated_phone_number = $mysqli->real_escape_string($_POST['phone_number']);
    $updated_address = $mysqli->real_escape_string($_POST['address']);
    $updated_role = $_POST['role'] ?? 'Customer';
    $update_query = "UPDATE users 
                     SET first_name = ?, 
                         last_name = ?, 
                         email = ?, 
                         phone_number = ?, 
                         address = ?, 
                         role = ? 
                     WHERE id = ?";
    $update_stmt = $mysqli->prepare($update_query);
    $update_stmt->bind_param(
        "ssssssi",
        $updated_first_name,
        $updated_last_name,
        $updated_email,
        $updated_phone_number,
        $updated_address,
        $updated_role,
        $customer_id
    );
    if ($update_stmt->execute()) {
        echo "<script>alert('تم تحديث بيانات العميل بنجاح.');</script>";
    } else {
        echo "<script>alert('حدث خطأ أثناء التحديث: " . $mysqli->error . "');</script>";
    }

    $update_stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_customer'])) {
    $customer_id = $mysqli->real_escape_string($_POST['customer_id']);
    $delete_query = "DELETE FROM users WHERE id = ?";
    $delete_stmt = $mysqli->prepare($delete_query);
    $delete_stmt->bind_param("i", $customer_id);
    if ($delete_stmt->execute()) {
        echo "<script>alert('تم حذف الحساب بنجاح.');</script>";
    } else {
        echo "<script>alert('حدث خطأ أثناء الحذف: " . $mysqli->error . "');</script>";
    }
    $delete_stmt->close();
}
$customers_per_page = 8;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page_offset = ($current_page - 1) * $customers_per_page;
$select_customers_query = "SELECT id, first_name, last_name, email, phone_number, address, role, profile_picture 
                           FROM users 
                           WHERE role = 'Customer' 
                           LIMIT ? OFFSET ?";
$select_customers_stmt = $mysqli->prepare($select_customers_query);
$select_customers_stmt->bind_param("ii", $customers_per_page, $page_offset);
$select_customers_stmt->execute();
$customers_result = $select_customers_stmt->get_result();
$count_query = "SELECT COUNT(*) AS total FROM users WHERE role = 'customer'";
$customers_count = $mysqli->query($count_query)->fetch_assoc()['total'];
$total_pages = ceil($customers_count / $customers_per_page);
?>
<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إدارة العملاء</title>
  <?php include("admin-header.php"); ?>
  <?php include("admin-sidebar.php"); ?>
  <style>
    main {
      flex: 1;
      padding: 20px;
      z-index: 0;
    }
    main header {
      text-align: center;
      margin-bottom: 20px;
    }
    main header h1 {
      font-size: 1.5rem;
    }
    main header h1 span {
      color: #224F34;
      font-size: 1.2rem;
    }
    .customer-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-end;
      gap: 15px;
      margin-right:20px;
    }
    .customer-card {
      background: linear-gradient(to bottom, #F7F3EB 48%, #FEFCF9 40%);
      border: 1px solid rgb(201, 193, 182);
      border-radius: 30px;
      text-align: center;
      padding: 15px;
      box-shadow: 0 4px 9px rgba(0, 0, 0, 0.1);
      position: relative;
      width: 260px;
      height: 323px;
    }
    .customer-card img {
      position: relative;
      top: 20px;
      margin-bottom: 48px;
      object-fit: cover;
    }
    a {
      text-decoration: none;
      outline: none;
    }
    .customer-card h3 {
      font-size: 1.5em;
margin-top:13px;
      color: #725C3A;
    }
    .customer-card p {
      margin: -33px 0;
      color: #555;
    }
    .customer-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .edit-btn {
      position: absolute;
      top: 30px;
      left: 221px;
      background: none;
      border: none;
      cursor: pointer;
      padding: 0;
      z-index: 2;
    }
    .edit-btn img {
      width: 24.8px;
      height: 24.8px;
      transition: transform 0.3s ease;
    }
    .edit-btn:hover img {
      transform: scale(1.1);
    }
    .delete-btn {
      position: absolute;
      top: -10px;
      right: 8px;
      background: none;
      border: none;
      cursor: pointer;
      padding: 5px;
      z-index: 1;
    }
    .delete-btn img {
      width: 25.2px;
      height: 25.2px;
      transition: transform 0.2s ease-in-out;
    }
    .delete-btn:hover img {
      transform: scale(1.1);
    }
    .pagination {
    text-align: center;
    margin-top: 20px;
    position: fixed; 
    bottom: 0; 
    left: 0; 
    width: 100%; 
    z-index: 1000; 
}
    .pagination a {
      text-decoration: none;
      color: #7b612b;
      margin: 0 5px;
      padding: 5px 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      transition: background-color 0.3s;
    }
    .pagination a:hover {
      background-color: #d4c9b6;
    }
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.2);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    .modal-content {
      width: 384px;
      height: 660px;
      margin: 13px auto;
      background-color: #fbf9f5;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
      direction: rtl;
      text-align: center;
      position: relative;
    }
    .modal-content .close-btn {
      position: absolute;
      top: 10px;
      left: 14px;
      width: 39px;
      height: 39px;
      cursor: pointer;
      transition: transform 0.3s ease;
    }
    .modal-content .close-btn:hover {
      transform: scale(1.1);
    }
    .modal-content form {
      width: 100%;
    }
    .modal-content label {
      margin-top: 20px;
      text-align: right;
      margin-right: 38px;
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #224F34;
      font-size: 18px;
      cursor: pointer;
    }
    .modal-content .input {
      max-width: 279px;
      background-color: #F7F5F0;
      color: rgb(33, 28, 20);
      padding: 0.1rem 0.5rem;
      min-height: 44px;
      border-radius: 40px;
      font-size: 16px;
      width: 100%;
      margin-bottom: -4px;
      padding-right: 18px;
      outline: 1px solid rgb(187, 185, 180);
    }
    .modal-content .input:hover {
      outline: 1px solid rgb(109, 103, 95);
    }
    .modal-content select.input {
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      background: url('images/arrow-down.png') no-repeat;
      background-position: left 18px center;
      background-size: 16px 16px;
      width: 100%;
      max-width: 279px;
      background-color: #F7F5F0;
      color: rgb(33, 28, 20);
      padding: 0.1rem 0.5rem;
      padding-left: 40px;
      padding-right: 18px;
      min-height: 44px;
      border-radius: 40px;
      font-size: 16px;
      outline: 1px solid rgb(187, 185, 180);
    }
    .modal-content select.input:hover {
      outline: 1px solid rgb(109, 103, 95);
    }
    .modal-content .update-btn {
      transition: all 0.3s ease-in-out;
      width: 145px;
      height: 45px;
      background-color: #725C3A;
      border-radius: 40px;
      box-shadow: 0 15px 25px -6px rgba(114, 92, 58, 0.5);
      outline: none;
      cursor: pointer;
      border: none;
      font-size: 24px;
      color: white;
      display: block;
      margin: 30px auto 0;
    }
    .modal-content .update-btn:hover {
      transform: translateY(3px);
      box-shadow: none;
      background-color: #5E4C2A;
    }
    .modal-content .update-btn:active {
      opacity: 0.5;
    }
    h1 {
      font-size: 28px !important;
      font-weight: bold !important;
      color: #224F34 !important;
      text-align: center !important;
      padding: 10px 0 !important;
      margin-left: 30px !important;
      margin-top: -20px !important;
    }
    .info {
      display: flex;
      align-items: center;
      direction: ltr;
      margin: 0;
      justify-content: center;
      gap: 0px;
    }
    .info .icon {
      width: 20px;
      height: 20px;
      margin-left: 7px;
      top: 23px;
    }
    .count-number {
      font-size: 1.5rem;
      font-weight: bold;
    }
    .confirm-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.2);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    .confirm-modal-content {
      width: 320px;
      background-color: #fbf9f5;
      padding: 33px;
      border-radius: 20px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
      direction: rtl;
      text-align: center;
      position: relative;
    }
    .confirm-modal-content h2 {
      font-size: 18px;
      margin-bottom: 20px;
      color: #224F34;
      margin-left: 49px;
      white-space: nowrap;
    }
    .btn-container {
      display: flex;
      gap: 20px;
      justify-content: center;
      width: 100%;
    }
    .confirm-modal-content button {
      padding: 10px 20px;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
    }
    .btn-yes {
      background-color: #725C3A;
      color: white;
    }
    .btn-yes:hover {
      transform: scale(1.1);
    }
    .btn-no {
      background-color: #ddd;
      color: #224F34;
    }
    .btn-no:hover {
      transform: scale(1.1);
    }
  </style>
</head>
<body>
<main>
  <header>
    <h1>العمـلاء ( <?php echo $customers_count; ?> )</h1>
  </header>
  <section class="customer-grid">
  <?php
while ($customer = $customers_result->fetch_assoc()) {
  echo '<div class="customer-card">';
  echo '<a href="customer_details.php?id=' . $customer['id'] . '">';
  
  if (!empty($customer['profile_picture'])) {
    $img_src = htmlspecialchars($customer['profile_picture']);
  } else {
    $img_src = "uploads/default-profile.png";
  }
  echo '<img src="' . $img_src . '" alt="صورة العميل" style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover;">';
  
  echo '<h3>' . htmlspecialchars($customer['first_name']) . '</h3>';
  echo '<p class="info">'
       . htmlspecialchars($customer['phone_number'])
       . ' <img src="images/num.png" alt="رقم الهاتف" class="icon">'
       . '</p>';
  echo '<p class="info">'
       . htmlspecialchars($customer['email'])
       . ' <img src="images/gmail.png" alt="البريد الإلكتروني" class="icon">'
       . '</p>';
  echo '</a>';
  echo '<button class="edit-btn" onclick="openModal(
            \'' . $customer['id'] . '\',
            \'' . htmlspecialchars($customer['first_name']) . '\',
            \'' . htmlspecialchars($customer['last_name']) . '\',
            \'' . htmlspecialchars($customer['email']) . '\',
            \'' . htmlspecialchars($customer['phone_number']) . '\',
            \'' . htmlspecialchars($customer['address']) . '\',
            \'' . htmlspecialchars($customer['role']) . '\'
          )">
          <img src="images/edit111.png" alt="تعديل">
        </button>';
  echo '<button class="delete-btn" onclick="openDeleteModal(' . $customer['id'] . ')">
          <img src="images/del.png" alt="حذف">
        </button>';
  echo '</div>';
}
?>

  </section>
  <footer class="pagination">
    <?php
    for ($i = 1; $i <= $total_pages; $i++) {
      echo '<a href="?page=' . $i . '">' . $i . '</a>';
    }
    ?>
  </footer>
</main>
<form id="deleteForm" method="POST" style="display: none;">
  <input type="hidden" name="customer_id" id="deleteCustomerId">
  <input type="hidden" name="delete_customer" value="1">
</form>
<div id="deleteModal" class="confirm-modal">
  <div class="confirm-modal-content">
    <h2>هل أنت متأكد من حذف الحساب؟</h2>
    <div class="btn-container">
      <button class="btn-yes" onclick="confirmDeleteCustomer()">نعم</button>
      <button class="btn-no" onclick="closeDeleteModal()">لا</button>
    </div>
  </div>
</div>
<div class="modal" id="editModal">
  <div class="modal-content">
    <img src="images/close.png" alt="إغلاق" class="close-btn" onclick="closeModal()">
    <form method="POST">
      <input type="hidden" name="customer_id" id="customer_id" class="input">
      <label>الاسم الأول:</label>
      <input type="text" name="first_name" id="first_name" class="input" required>
      <label>اسم العائلة:</label>
      <input type="text" name="last_name" id="last_name" class="input" required>
      <label>البريد الإلكتروني:</label>
      <input type="email" name="email" id="email" class="input" required>
      <label>رقم الهاتف:</label>
      <input type="text" name="phone_number" id="phone_number" class="input" required>
      <label>العنوان:</label>
      <input type="text" name="address" id="address" class="input" required>
      <label>الدور:</label>
      <select name="role" id="role" class="input">
        <option value="Customer">زبون</option>
        <option value="Craftsman">حرفي</option>
        <option value="Admin">إداري</option>
      </select>
      <div class="buttons-container">
        <button type="submit" name="edit_customer" class="update-btn">تحديث</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openModal(id, firstName, lastName, email, phoneNumber, address, role) {
    document.getElementById('customer_id').value = id;
    document.getElementById('first_name').value = firstName;
    document.getElementById('last_name').value = lastName;
    document.getElementById('email').value = email;
    document.getElementById('phone_number').value = phoneNumber;
    document.getElementById('address').value = address;
    document.getElementById('role').value = role;
    document.getElementById('editModal').style.display = 'flex';
  }
  function closeModal() {
    document.getElementById('editModal').style.display = 'none';
  }
  window.onclick = function(event) {
    const modal = document.getElementById("editModal");
    if (event.target === modal) {
      modal.style.display = "none";
    }
  };

  let deleteCustomerId = null;
  function openDeleteModal(customerId) {
    deleteCustomerId = customerId;
    document.getElementById("deleteModal").style.display = "flex";
  }
  function closeDeleteModal() {
    document.getElementById("deleteModal").style.display = "none";
  }
  function confirmDeleteCustomer() {
    document.getElementById("deleteCustomerId").value = deleteCustomerId;
    document.getElementById("deleteForm").submit();
  }
</script>
</body>
</html>
