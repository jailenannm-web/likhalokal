<?php
declare(strict_types=1);

$pageTitle = 'Register Your Business';
require_once dirname(__DIR__) . '/bootstrap.php';

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
    <form action="process_registration.php" method="POST" enctype="multipart/form-data">
        
        <div class="info-section">
            <div class="section-header">
                <h2>BUSINESS INFORMATION</h2>
            </div>
            <div class="section-body">
                <div class="form-group">
                    <label>Business Name:</label>
                    <input type="text" name="biz_name" class="form-control" placeholder="Enter your business name" required>
                </div>

                <div class="form-group">
                    <label>Type of Business:</label>
                    <select name="biz_type" class="form-control" required>
                        <option value="Food">Food</option>
                        <option value="Retail">Retail</option>
                        <option value="Services">Services</option>
                        <option value="Accomodation">Accommodation</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Business Category:</label>
                    <select name="biz_category" class="form-control" required>
                        <option value="Products (Snacks, Produce, Pastries)">Products (Snacks, Produce, Pastries)</option>
                        <option value="Restaurants/Cafe">Restaurants/Cafe</option>
                        <option value="Resorts/Homestay">Resorts/Homestay</option>
                        <option value="Local Handicrafts">Local Handicrafts</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Business Address:</label>
                    <input type="text" name="biz_address" class="form-control" placeholder="Enter your business address" required>
                </div>

                <div class="form-group">
                    <label>Branch (if any):</label>
                    <input type="text" name="biz_branch" class="form-control" placeholder="">
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
                    <input type="text" name="owner_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Contact Number:</label>
                    <input type="text" name="owner_contact" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Email Address:</label>
                    <input type="email" name="owner_email" class="form-control" required>
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
                            <input type="file" id="id_front" name="id_front" style="display:none" accept="image/*" required>
                        </div>
                        <div class="upload-box" onclick="document.getElementById('id_back').click()">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <span>Upload Back ID</span>
                            <input type="file" id="id_back" name="id_back" style="display:none" accept="image/*" required>
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
                        <input type="file" id="product_img" name="product_img" style="display:none" accept="image/*">
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
                            <input type="checkbox" name="payments[]" value="COD">
                            <span class="checkmark"></span>
                        </label>
                        <label class="check-container">Gcash
                            <input type="checkbox" name="payments[]" value="Gcash">
                            <span class="checkmark"></span>
                        </label>
                        <label class="check-container">Maya
                            <input type="checkbox" name="payments[]" value="Maya">
                            <span class="checkmark"></span>
                        </label>
                        <label class="check-container">Bank Transfer
                            <input type="checkbox" name="payments[]" value="Bank Transfer">
                            <span class="checkmark"></span>
                        </label>
                        <label class="check-container">Pay Upon Booking
                            <input type="checkbox" name="payments[]" value="Pay Upon Booking">
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
                        <input type="file" id="biz_logo" name="biz_logo" style="display:none" accept="image/*">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 30px;">
                    <label>Short Business Description:</label>
                    <textarea name="biz_short_desc" class="form-control" style="height: 100px;"></textarea>
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
                        <input type="file" id="digital_sig" name="digital_sig" style="display:none" accept="image/*" required>
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

<?php require BASE_PATH . '/includes/footer.php'; ?>