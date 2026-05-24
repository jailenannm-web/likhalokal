<?php
declare(strict_types=1);

$pageTitle = 'Register Your Business';
require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';
require_once BASE_PATH . '/middleware/csrf.php';

if (!is_logged_in()) {
    set_flash('error', 'Please login or register to continue.');
    redirect(login_url_with_redirect(BASE_URL . 'register-business.php'));
}

$role = current_user_role();
if ($role === 'admin') {
    redirect(public_home_url());
}

$existingBusiness = null;
if (in_array($role, ['seller', 'local_user'], true)) {
    $existingBusiness = seller_business_for_user();
    if ($existingBusiness && $existingBusiness['status'] === 'approved') {
        redirect(public_home_url());
    }
    if ($role === 'seller' && $existingBusiness && $existingBusiness['status'] === 'pending' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(SELLER_URL . 'pending.php');
    }
}

function public_business_type_from_form(string $type): string
{
    return match ($type) {
        'food_vendor', 'craft_business', 'restaurant', 'travel_agency', 'resort', 'recreation', 'service', 'pasalubong', 'fresh_produce' => $type,
        'Food' => 'food_vendor',
        'Retail' => 'pasalubong',
        'Services' => 'service',
        'Accomodation', 'Accommodation' => 'resort',
        default => 'service',
    };
}

function public_business_payment_values(array $values): array
{
    $normalized = [];
    $map = [
        'COD' => 'Cash on pickup',
        'Cash on Delivery/Pickup' => 'Cash on pickup',
        'Cash on pickup' => 'Cash on pickup',
        'Gcash' => 'GCash',
        'GCash' => 'GCash',
        'Maya' => 'Maya',
        'Bank Transfer' => 'Bank transfer',
        'Bank transfer' => 'Bank transfer',
        'Pay Upon Booking' => 'Pay upon booking',
        'Pay upon booking' => 'Pay upon booking',
    ];

    foreach ($values as $value) {
        $value = trim((string) $value);
        if (isset($map[$value])) {
            $normalized[] = $map[$value];
        }
    }

    return array_values(array_unique($normalized));
}

function public_uploaded_file_from(array $keys): ?array
{
    foreach ($keys as $key) {
        if (isset($_FILES[$key]) && is_array($_FILES[$key]) && (int) ($_FILES[$key]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            return $_FILES[$key];
        }
    }

    return null;
}

$hasBusinessCategory = db_column_exists('businesses', 'business_category');
$hasBusinessBranch = db_column_exists('businesses', 'branch');
$formBusiness = $existingBusiness ?: [];
$currentUser = current_user();
$selectedPayments = json_decode((string) ($formBusiness['accepted_payments'] ?? '[]'), true);
if (!is_array($selectedPayments)) {
    $selectedPayments = [];
}
$selectedPayments = public_business_payment_values($selectedPayments);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token. Please try again.');
        redirect(BASE_URL . 'register-business.php');
    }

    $businessName = trim((string) ($_POST['business_name'] ?? $_POST['biz_name'] ?? ''));
    $businessType = public_business_type_from_form((string) ($_POST['business_type'] ?? $_POST['biz_type'] ?? 'service'));
    $businessCategory = trim((string) ($_POST['business_category'] ?? $_POST['biz_category'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? $_POST['biz_address'] ?? ''));
    $barangay = trim((string) ($_POST['barangay'] ?? ''));
    $branch = trim((string) ($_POST['branch'] ?? $_POST['biz_branch'] ?? ''));
    $ownerContact = trim((string) ($_POST['contact_number'] ?? $_POST['owner_contact'] ?? ''));
    $ownerEmail = trim((string) ($_POST['email'] ?? $_POST['owner_email'] ?? current_user()['email'] ?? ''));
    $latitude = trim((string) ($_POST['latitude'] ?? ''));
    $longitude = trim((string) ($_POST['longitude'] ?? ''));
    $operatingHours = trim((string) ($_POST['operating_hours'] ?? ''));
    $productList = trim((string) ($_POST['product_list'] ?? ''));
    $priceRange = trim((string) ($_POST['price_range'] ?? ''));
    $shortDescription = trim((string) ($_POST['description'] ?? $_POST['biz_short_desc'] ?? ''));
    $payments = json_encode(public_business_payment_values(array_values($_POST['pay'] ?? $_POST['payments'] ?? [])));
    $methods = array_values($_POST['methods'] ?? []);
    $descriptionParts = array_filter([
        $shortDescription,
        $productList !== '' ? 'Products/services: ' . $productList : '',
        $priceRange !== '' ? 'Price range: ' . $priceRange : '',
        !empty($methods) ? 'Order/booking methods: ' . implode(', ', $methods) : '',
    ]);
    $description = implode("\n", $descriptionParts);

    $errors = [];
    if ($businessName === '' || strlen($businessName) > 200) {
        $errors[] = 'Business name is required.';
    }
    if ($address === '') {
        $errors[] = 'Business address is required.';
    }
    if ($ownerEmail !== '' && !filter_var($ownerEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid owner email.';
    }
    if ($latitude !== '' && !is_numeric($latitude)) {
        $errors[] = 'Latitude must be a valid number.';
    }
    if ($longitude !== '' && !is_numeric($longitude)) {
        $errors[] = 'Longitude must be a valid number.';
    }
    if (strlen($branch) > 150) {
        $errors[] = 'Branch must be 150 characters or fewer.';
    }

    $logo = $existingBusiness['logo'] ?? null;
    $logoFile = public_uploaded_file_from(['logo', 'biz_logo']);
    if ($logoFile !== null) {
        $upload = save_upload($logoFile, 'businesses');
        if ($upload) {
            $logo = $upload;
        } else {
            $errors[] = 'Business logo must be a valid image under the upload limit.';
        }
    }

    $cover = $existingBusiness['cover_image'] ?? null;
    $coverFile = public_uploaded_file_from(['cover_image', 'product_img']);
    if ($coverFile !== null) {
        $upload = save_upload($coverFile, 'businesses');
        if ($upload) {
            $cover = $upload;
        } else {
            $errors[] = 'Business cover image must be a valid image under the upload limit.';
        }
    }

    if (!empty($errors)) {
        set_flash('error', implode(' ', $errors));
        redirect(BASE_URL . 'register-business.php');
    }

    $fullAddress = $hasBusinessBranch ? $address : trim($address . ($branch !== '' ? ' - ' . $branch : ''));

    if ($existingBusiness && in_array($existingBusiness['status'], ['rejected', 'pending'], true)) {
        $sql = 'UPDATE businesses SET business_name=?, business_type=?, description=?, contact_number=?, email=?, address=?, barangay=?, latitude=?, longitude=?, operating_hours=?, accepted_payments=?, logo=?, cover_image=?, status=\'pending\', rejection_reason=NULL, approved_by=NULL, approved_at=NULL';
        $params = [$businessName, $businessType, $description, $ownerContact, $ownerEmail, $fullAddress, $barangay, $latitude !== '' ? $latitude : null, $longitude !== '' ? $longitude : null, $operatingHours, $payments, $logo, $cover];
        if ($hasBusinessCategory) {
            $sql .= ', business_category=?';
            $params[] = $businessCategory;
        }
        if ($hasBusinessBranch) {
            $sql .= ', branch=?';
            $params[] = $branch !== '' ? $branch : null;
        }
        $sql .= ', updated_at=NOW() WHERE id=? AND user_id=?';
        $params[] = (int) $existingBusiness['id'];
        $params[] = current_user_id();
        db()->prepare($sql)->execute($params);
    } else {
        $columns = 'user_id, business_name, business_type, description, contact_number, email, address, barangay, latitude, longitude, operating_hours, accepted_payments, logo, cover_image, status, created_at, updated_at';
        $marks = '?,?,?,?,?,?,?,?,?,?,?,?,?,?,' . "'pending',NOW(),NOW()";
        $params = [current_user_id(), $businessName, $businessType, $description, $ownerContact, $ownerEmail, $fullAddress, $barangay, $latitude !== '' ? $latitude : null, $longitude !== '' ? $longitude : null, $operatingHours, $payments, $logo, $cover];
        if ($hasBusinessCategory) {
            $columns = 'user_id, business_name, business_type, business_category, description, contact_number, email, address, barangay, latitude, longitude, operating_hours, accepted_payments, logo, cover_image, status, created_at, updated_at';
            $marks = '?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,' . "'pending',NOW(),NOW()";
            $params = [current_user_id(), $businessName, $businessType, $businessCategory, $description, $ownerContact, $ownerEmail, $fullAddress, $barangay, $latitude !== '' ? $latitude : null, $longitude !== '' ? $longitude : null, $operatingHours, $payments, $logo, $cover];
        }
        if ($hasBusinessBranch) {
            $columns .= ', branch';
            $marks .= ',?';
            $params[] = $branch !== '' ? $branch : null;
        }
        db()->prepare('INSERT INTO businesses (' . $columns . ') VALUES (' . $marks . ')')->execute($params);
    }

    set_flash('success', 'Your business registration is under review. Please wait for admin approval before accessing seller features.');
    redirect(public_home_url());
}

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Bungee&family=Inter:wght@400;600;800&family=Lisu+Bosa:wght@400;500&display=swap" rel="stylesheet">

<style>
    :root {
        --amber-orange: #f2a63d;
        --dark-navy: #051024;
        --light-blue-bg: #a8e0ff;
        --border-color: #ced4da;
    }

    body {
        background-color: var(--light-blue-bg);
        font-family: 'Inter', sans-serif;
    }

    .main-form-wrapper {
        max-width: 900px;
        margin: 50px auto;
        padding: 0 20px;
    }

    /* Section Cards */
    .info-section {
        background: white;
        border: 1px solid #ddd;
        border-radius: 12px;
        margin-bottom: 30px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .section-header {
        background-color: #fff;
        border-bottom: 1px solid #ddd;
        padding: 15px 25px;
    }

    .section-header h2 {
        font-family: 'Bungee', cursive;
        color: var(--dark-navy);
        font-size: 1.5rem;
        margin: 0;
        letter-spacing: 1px;
    }

    .section-body {
        padding: 30px 40px;
    }

    /* Layout Grids */
    .form-group {
        display: grid;
        grid-template-columns: 200px 1fr;
        align-items: center;
        margin-bottom: 20px;
        gap: 20px;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
        font-size: 1.1rem;
    }

    /* Inputs & Selects */
    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-family: 'Lisu Bosa', serif;
        font-size: 1.1rem;
        color: #555;
        background-color: #fff;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--dark-navy);
        box-shadow: 0 0 5px rgba(5, 16, 36, 0.1);
    }

    /* ID Upload Box Styles */
    .upload-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 20px;
    }

    .upload-box {
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: background 0.3s;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .upload-box:hover {
        background-color: #f8f9fa;
    }

    .upload-box i {
        font-size: 3rem;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .upload-box span {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
    }

    /* Submit Section */
    .action-wrap {
        text-align: center;
        margin-bottom: 50px;
    }

    .submit-btn {
        background-color: var(--dark-navy);
        color: white;
        font-family: 'Bungee', cursive;
        padding: 15px 80px;
        border: none;
        border-radius: 10px;
        font-size: 1.3rem;
        cursor: pointer;
        box-shadow: 0 5px 0px #000;
        transition: all 0.2s;
    }

    .submit-btn:hover {
        background-color: var(--amber-orange);
        color: var(--dark-navy);
        transform: translateY(2px);
        box-shadow: 0 3px 0px #c98220;
    }

    /* Custom Checkbox Styles */
    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .check-container {
        display: block;
        position: relative;
        padding-left: 35px;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        font-size: 1.1rem;
        user-select: none;
        line-height: 25px;
    }

    /* Hide default checkbox */
    .check-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    /* Create custom checkmark */
    .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 22px;
        width: 22px;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }

    /* On mouse-over, add background color */
    .check-container:hover input ~ .checkmark {
        background-color: #f1f1f1;
    }

    /* When checked, add navy color */
    .check-container input:checked ~ .checkmark {
        background-color: var(--dark-navy);
        border-color: var(--dark-navy);
    }

    /* Create the checkmark indicator */
    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    /* Show indicator when checked */
    .check-container input:checked ~ .checkmark:after {
        display: block;
    }

    /* Style the indicator */
    .check-container .checkmark:after {
        left: 7px;
        top: 3px;
        width: 6px;
        height: 11px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    @media (max-width: 768px) {
        .form-group { grid-template-columns: 1fr; gap: 5px; }
        .upload-container { grid-template-columns: 1fr; }
    }
</style>

<div class="main-form-wrapper">
    <?php if ($m = flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
    <?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="info-section">
            <div class="section-header">
                <h2>BUSINESS INFORMATION</h2>
            </div>
            <div class="section-body">
                <div class="form-group">
                    <label>Business Name:</label>
                    <input type="text" name="business_name" class="form-control" placeholder="Enter your business name" required value="<?= e((string) ($formBusiness['business_name'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Type of Business:</label>
                    <select name="business_type" class="form-control" required>
                        <?php foreach (['food_vendor','craft_business','restaurant','travel_agency','resort','recreation','service','pasalubong','fresh_produce'] as $type): ?>
                            <option value="<?= e($type) ?>" <?= (($formBusiness['business_type'] ?? '') === $type) ? 'selected' : '' ?>><?= e(business_type_label($type)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Business Category:</label>
                    <input type="text" name="business_category" class="form-control" required placeholder="Example: Pasalubong, Fresh Produce, Tours" value="<?= e((string) ($formBusiness['business_category'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Business Address:</label>
                    <input type="text" name="address" class="form-control" placeholder="Enter your business address" required value="<?= e((string) ($formBusiness['address'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Barangay:</label>
                    <input type="text" name="barangay" class="form-control" placeholder="Example: Poblacion" value="<?= e((string) ($formBusiness['barangay'] ?? '')) ?>">
                </div>

                <?php if ($hasBusinessBranch): ?>
                <div class="form-group">
                    <label>Branch (if any):</label>
                    <input type="text" name="branch" class="form-control" maxlength="150" placeholder="Optional branch or landmark" value="<?= e((string) ($formBusiness['branch'] ?? '')) ?>">
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Operating Hours:</label>
                    <input type="text" name="operating_hours" class="form-control" placeholder="Example: Mon-Sat 8:00 AM - 6:00 PM" value="<?= e((string) ($formBusiness['operating_hours'] ?? '')) ?>">
                </div>

                <div class="form-group" style="align-items: flex-start;">
                    <label style="margin-top: 10px;">Map Location:</label>
                    <div>
                        <div id="businessMapPicker" class="lk-map-picker" data-map-picker data-lat-input="businessLatitude" data-lng-input="businessLongitude" style="height:350px;border-radius:12px;overflow:hidden;border:1px solid #ced4da;"></div>
                        <p class="small text-muted mt-2 mb-0">Tap the map or edit the coordinates to set the business location.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label>Latitude:</label>
                    <input type="text" name="latitude" id="businessLatitude" class="form-control" placeholder="14.1720" value="<?= e((string) ($formBusiness['latitude'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Longitude:</label>
                    <input type="text" name="longitude" id="businessLongitude" class="form-control" placeholder="122.9450" value="<?= e((string) ($formBusiness['longitude'] ?? '')) ?>">
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="section-header">
                <h2>OWNER INFORMATION</h2>
            </div>
            <div class="section-body">
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" name="owner_name" class="form-control" required value="<?= e((string) ($currentUser['full_name'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Contact Number:</label>
                    <input type="text" name="contact_number" class="form-control" required value="<?= e((string) ($formBusiness['contact_number'] ?? $currentUser['contact_number'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Email Address:</label>
                    <input type="email" name="email" class="form-control" required value="<?= e((string) ($formBusiness['email'] ?? $currentUser['email'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Type of Valid ID:</label>
                    <select name="id_type" class="form-control" required>
                        <option value="Philippine National ID">Philippine National ID</option>
                        <option value="Driver's License">Driver's License</option>
                        <option value="Passport">Passport</option>
                        <option value="UMID">UMID</option>
                        <option value="Voter's ID">Voter's ID</option>
                    </select>
                </div>

                <div class="form-group" style="align-items: flex-start;">
                    <label style="margin-top: 10px;">Valid ID Upload:</label>
                    <div class="upload-container">
                        <div class="upload-box" onclick="document.getElementById('id_front').click()">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <span>Upload Front ID</span>
                            <input type="file" id="id_front" name="id_front" style="display:none" accept="image/jpeg,image/png,image/webp">
                        </div>
                        <div class="upload-box" onclick="document.getElementById('id_back').click()">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <span>Upload Back ID</span>
                            <input type="file" id="id_back" name="id_back" style="display:none" accept="image/jpeg,image/png,image/webp">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="section-header">
                <h2>PRODUCTS/SERVICES TO FEATURE</h2>
            </div>
            <div class="section-body">
                <div class="form-group">
                    <label>Product/Service List:</label>
                    <input type="text" name="product_list" class="form-control" placeholder="">
                </div>

                <div class="form-group" style="align-items: flex-start;">
                    <label style="margin-top: 10px;">Upload Images:</label>
                    <div class="upload-box" style="width: 100%; height: 200px;" onclick="document.getElementById('product_img').click()">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span>Upload Product Image</span>
                        <input type="file" id="product_img" name="cover_image" style="display:none" accept="image/jpeg,image/png,image/webp">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 25px;">
                    <label>Price Range:</label>
                    <select name="price_range" class="form-control">
                        <option value="Php 20-200">Php 20-200</option>
                        <option value="Php 201-500">Php 201-500</option>
                        <option value="Php 501-1000">Php 501-1000</option>
                        <option value="Php 1000+">Php 1000+</option>
                    </select>
                </div>

                <div class="form-group" style="align-items: flex-start;">
                    <label>Available Order/Booking Methods:</label>
                    <div class="checkbox-group">
                        <label class="check-container">Store Pickup
                            <input type="checkbox" name="methods[]" value="Store Pickup">
                            <span class="checkmark"></span>
                        </label>
                        <label class="check-container">Delivery
                            <input type="checkbox" name="methods[]" value="Delivery">
                            <span class="checkmark"></span>
                        </label>
                        <label class="check-container">Online Booking
                            <input type="checkbox" name="methods[]" value="Online Booking">
                            <span class="checkmark"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="section-header">
                <h2>PAYMENT METHODS</h2>
            </div>
            <div class="section-body">
                <div class="form-group" style="align-items: flex-start;">
                    <label>Choose Accepted Payments:</label>
                    <div class="checkbox-group">
                        <label class="check-container">Cash on Delivery/Pickup
                            <input type="checkbox" name="pay[]" value="Cash on pickup" <?= in_array('Cash on pickup', $selectedPayments, true) ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                        </label>
                        <label class="check-container">Gcash
                            <input type="checkbox" name="pay[]" value="GCash" <?= in_array('GCash', $selectedPayments, true) ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                        </label>
                        <label class="check-container">Maya
                            <input type="checkbox" name="pay[]" value="Maya" <?= in_array('Maya', $selectedPayments, true) ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                        </label>
                        <label class="check-container">Bank Transfer
                            <input type="checkbox" name="pay[]" value="Bank transfer" <?= in_array('Bank transfer', $selectedPayments, true) ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                        </label>
                        <label class="check-container">Pay Upon Booking
                            <input type="checkbox" name="pay[]" value="Pay upon booking" <?= in_array('Pay upon booking', $selectedPayments, true) ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="section-header">
                <h2>BUSINESS LOGO</h2>
            </div>
            <div class="section-body">
                <div class="form-group" style="align-items: flex-start;">
                    <label style="margin-top: 10px;">Logo Upload:</label>
                    <div class="upload-box" style="width: 100%; height: 220px;" onclick="document.getElementById('biz_logo').click()">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span>Upload Logo</span>
                        <input type="file" id="biz_logo" name="logo" style="display:none" accept="image/jpeg,image/png,image/webp">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 30px;">
                    <label>Short Business Description:</label>
                    <textarea name="description" class="form-control" style="height: 100px;"><?= e((string) ($formBusiness['description'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="section-header">
                <h2>TERMS & AGREEMENTS</h2>
            </div>
            <div class="section-body">
                <div class="form-group" style="grid-template-columns: 1fr; align-items: flex-start;">
                    <label style="margin-bottom: 15px;">Vendor must check:</label>
                    <div class="checkbox-group">
                        <label class="check-container">I confirm that all information provided is true
                            <input type="checkbox" name="terms[]" value="info_true" required>
                            <span class="checkmark"></span>
                        </label>
                        <label class="check-container">I allow my products and information to be displayed on the website
                            <input type="checkbox" name="terms[]" value="allow_display" required>
                            <span class="checkmark"></span>
                        </label>
                        <label class="check-container">I agree to follow the marketplace rules and selling policies
                            <input type="checkbox" name="terms[]" value="agree_rules" required>
                            <span class="checkmark"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="section-header">
                <h2>CONFIRMATION</h2>
            </div>
            <div class="section-body">
                <div class="form-group" style="grid-template-columns: 1fr; align-items: flex-start;">
                    <label style="margin-bottom: 15px;">Owner’s Digital Signature over Printed Name</label>
                    <div class="upload-box" style="width: 100%; height: 280px;" onclick="document.getElementById('digital_sig').click()">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span>Upload Image</span>
                        <input type="file" id="digital_sig" name="digital_sig" style="display:none" accept="image/jpeg,image/png,image/webp">
                    </div>
                </div>
            </div>
        </div>


        <div class="action-wrap">
            <button type="submit" class="submit-btn">SUBMIT APPLICATION</button>
        </div>

    </form>
</div>

<script>
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            if(this.files && this.files[0]) {
                const span = this.parentElement.querySelector('span');
                span.innerText = "File: " + this.files[0].name;
                span.style.color = "#28a745";
            }
        });
    });
</script>

<script>
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            if(this.files && this.files[0]) {
                const span = this.parentElement.querySelector('span');
                // Display the filename inside the upload box upon selection
                span.innerText = "Selected: " + this.files[0].name;
                span.style.color = "#f2a63d"; // Use your amber-orange for visibility
                this.parentElement.style.borderColor = "#f2a63d";
            }
        });
    });
</script>

<?= map_picker_footer_scripts() ?>

<?php require BASE_PATH . '/includes/footer.php'; ?>
