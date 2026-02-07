<?php
session_start();
include "../db.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>NEP 2020 Student Course Form</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Poppins',sans-serif;
    background:#f4f6fb;
}
.card{
    max-width:950px;
    margin:40px auto;
    border:none;
    border-radius:20px;
    box-shadow:0 15px 40px rgba(0,0,0,0.1);
}
.card-header{
    background:#4e46e5;
    color:#fff;
    font-weight:600;
    border-radius:20px 20px 0 0;
}
.section-title{
    color:#4e46e5;
    font-weight:600;
    margin-top:30px;
}
.form-control,.form-select{
    border-radius:12px;
}
</style>
</head>

<body>

<div class="card">
<div class="card-header">Student Details & Course Selection (NEP 2020)</div>
<div class="card-body">

<form method="POST" action="save_form.php" onsubmit="return validateForm()">

<!-- ================= STUDENT DETAILS ================= -->
<h5 class="section-title">Student Details</h5>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Full Name</label>
        <input type="text" id="name" name="name" class="form-control" required placeholder="Enter full name">
    </div>

    <div class="col-md-6">
        <label class="form-label">Mobile Number</label>
        <input type="text" id="mobile" name="mobile" class="form-control" maxlength="10"
               required placeholder="10 digit mobile number">
    </div>

    <div class="col-md-6">
        <label class="form-label">ABC ID</label>
        <input type="text" id="abc_id" name="abc_id" class="form-control"
               required placeholder="e.g. ABC12345">
    </div>

    <div class="col-md-6">
        <label class="form-label">Select Academic Year</label>
        <select id="year" name="year" class="form-select" required onchange="loadSubjects()">
            <option value="">-- Select Year --</option>
            <option value="FY">First Year (FY)</option>
            <option value="SY">Second Year (SY)</option>
            <option value="TY">Third Year (TY)</option>
        </select>
    </div>
</div>

<!-- ================= SUBJECT SECTION ================= -->
<div id="subjectSection"></div>

<button class="btn btn-primary rounded-pill px-5 mt-4">
    Submit & Continue
</button>

</form>

</div>
</div>

<script>
function loadSubjects(){
    const year = document.getElementById("year").value;
    let html = "";

    if(year === "FY"){
        html = `
        <h5 class="section-title">FY Subjects</h5>

        <select class="form-select mb-2" name="course1" required>
            <option value="">Course 1</option>
            <option>Mathematics</option>
            <option>Physics</option>
            <option>Chemistry</option>
        </select>

        <select class="form-select mb-2" name="course2" required>
            <option value="">Course 2</option>
            <option>Computer Science</option>
            <option>Biology</option>
            <option>Statistics</option>
        </select>

        <select class="form-select mb-2" name="course3" required>
            <option value="">Course 3</option>
            <option>Environmental Science</option>
            <option>Basic Electronics</option>
            <option>Data Literacy</option>
        </select>

        <select class="form-select mb-2" name="oe" required>
            <option value="">Open Elective (OE)</option>
            <option>Indian Knowledge System</option>
            <option>Yoga & Wellness</option>
        </select>

        <select class="form-select mb-2" name="ks" required>
            <option value="">KS / IKS</option>
            <option>Indian Constitution</option>
            <option>Ethics & Values</option>
        </select>
        `;
    }

    if(year === "SY"){
        html = `
        <h5 class="section-title">SY Subjects</h5>

        <select class="form-select mb-2" name="major" required>
            <option value="">Major</option>
            <option>Computer Science</option>
            <option>Physics</option>
            <option>Mathematics</option>
        </select>

        <select class="form-select mb-2" name="minor" required>
            <option value="">Minor</option>
            <option>Data Science</option>
            <option>Electronics</option>
            <option>Statistics</option>
        </select>

        <select class="form-select mb-2" name="oe" required>
            <option value="">Open Elective (OE)</option>
            <option>AI Basics</option>
            <option>Green Technology</option>
        </select>

        <select class="form-select mb-2" name="vsc" required>
            <option value="">VSC</option>
            <option>Python Programming</option>
            <option>Web Development</option>
        </select>

        <select class="form-select mb-2" name="english" required>
            <option>English Communication</option>
        </select>

        <select class="form-select mb-2" name="cc" required>
            <option>Indian Constitution</option>
        </select>
        `;
    }

    if(year === "TY"){
        html = `
        <h5 class="section-title">TY Subjects</h5>

        <select class="form-select mb-2" name="major1" required>
            <option value="">Major 1</option>
            <option>Data Science</option>
            <option>Advanced Physics</option>
        </select>

        <select class="form-select mb-2" name="major2" required>
            <option value="">Major 2</option>
            <option>AI & Machine Learning</option>
            <option>Applied Mathematics</option>
        </select>

        <select class="form-select mb-2" name="oe" required>
            <option>Entrepreneurship</option>
            <option>Research Methodology</option>
        </select>

        <select class="form-select mb-2" name="vsc" required>
            <option>Cloud Computing</option>
            <option>Advanced Web Tech</option>
        </select>

        <select class="form-select mb-2" name="english" required>
            <option>Professional English</option>
        </select>

        <select class="form-select mb-2" name="ojt" required>
            <option>Industry Internship</option>
            <option>Research Project</option>
        </select>
        `;
    }

    document.getElementById("subjectSection").innerHTML = html;
}

/* ================= VALIDATIONS ================= */
function validateForm(){
    const name = document.getElementById("name").value.trim();
    const mobile = document.getElementById("mobile").value.trim();
    const abc = document.getElementById("abc_id").value.trim();

    if(!/^[A-Za-z ]+$/.test(name)){
        alert("Name should contain only letters and spaces");
        return false;
    }
    if(!/^[0-9]{10}$/.test(mobile)){
        alert("Mobile number must be exactly 10 digits");
        return false;
    }
    if(!/^[A-Za-z0-9]{6,}$/.test(abc)){
        alert("ABC ID must be at least 6 alphanumeric characters");
        return false;
    }
    return true;
}
</script>

</body>
</html>
