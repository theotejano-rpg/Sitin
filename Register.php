<?php
session_start();
require_once 'db.php';

$nav_active = 'register';

$errors  = [];
$old     = [];
$success = '';

$course_options = [
    'BSIT'  => 'BS Information Technology',
    'BSCS'  => 'BS Computer Science',
    'BSIS'  => 'BS Information Systems',
    'BSECE' => 'BS Electronics Engineering',
    'ACT'   => 'Associate in Computer Technology',
];
$level_options = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name       = trim($_POST['first_name']       ?? '');
    $middle_name      = trim($_POST['middle_name']      ?? '');
    $last_name        = trim($_POST['last_name']        ?? '');
    $student_id       = trim($_POST['student_id']       ?? '');
    $course           = trim($_POST['course']           ?? '');
    $level            = trim($_POST['level']            ?? '');
    $email            = strtolower(trim($_POST['email'] ?? ''));
    $address          = trim($_POST['address']          ?? '');
    $password         = $_POST['password']              ?? '';
    $confirm_password = $_POST['confirm_password']      ?? '';

    $old = compact('first_name','middle_name','last_name','student_id','course','level','email','address');

    if ($first_name === '')       $errors['first_name']       = 'First name is required.';
    if ($last_name  === '')       $errors['last_name']        = 'Last name is required.';
    if ($student_id === '')       $errors['student_id']       = 'Student ID is required.';
    if ($course     === '')       $errors['course']           = 'Please select your course.';
    if ($level      === '')       $errors['level']            = 'Please select your year level.';
    if ($email      === '')       $errors['email']            = 'UC Email address is required.';
    if ($address    === '')       $errors['address']          = 'Address is required.';
    if ($password   === '')       $errors['password']         = 'Please create a password.';
    if ($confirm_password === '') $errors['confirm_password'] = 'Please confirm your password.';

    if (!isset($errors['student_id']) && !preg_match('/^\d{4}-\d{5}$/', $student_id))
        $errors['student_id'] = 'Use the format 2025-XXXXX (e.g. 2025-00001).';
    if (!isset($errors['course']) && !array_key_exists($course, $course_options))
        $errors['course'] = 'Please select a valid course.';
    if (!isset($errors['level']) && !in_array($level, $level_options, true))
        $errors['level'] = 'Please select a valid year level.';
    if (!isset($errors['email']) && !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Please enter a valid email address.';
    if (!isset($errors['email']) && !str_ends_with($email, '@uc.edu.ph'))
        $errors['email'] = 'Please use your UC email (e.g. you@uc.edu.ph).';
    if (!isset($errors['password']) && strlen($password) < 8)
        $errors['password'] = 'Password must be at least 8 characters.';
    if (!isset($errors['confirm_password']) && !isset($errors['password']) && $password !== $confirm_password)
        $errors['confirm_password'] = 'Passwords do not match. Please try again.';

    if (empty($errors)) {
        $db  = get_db();
        $chk = $db->prepare('SELECT student_id, email FROM students WHERE student_id = ? OR email = ? LIMIT 1');
        $chk->execute([$student_id, $email]);
        $existing = $chk->fetch();
        if ($existing) {
            if ($existing['student_id'] === $student_id) $errors['student_id'] = 'This Student ID is already registered.';
            if ($existing['email']      === $email)      $errors['email']      = 'This email is already registered.';
        }
    }

    if (empty($errors)) {
        $db = get_db();
        $db->prepare("
            INSERT INTO students
                (first_name,middle_name,last_name,student_id,course,course_code,level,email,address,password)
            VALUES (?,?,?,?,?,?,?,?,?,?)
        ")->execute([
            $first_name, $middle_name, $last_name, $student_id,
            $course_options[$course], $course, $level, $email,
            $address, password_hash($password, PASSWORD_DEFAULT),
        ]);
        $success = "Account created for {$first_name} {$last_name}! You can now <a href=\"Login.php\">sign in</a>.";
        $old = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UC CCS &mdash; Register</title>
  <link rel="stylesheet" href="css/Style.css"/>
</head>
<body class="auth">

<?php include __DIR__ . '/nav_landing.php'; ?>

<?php if (!empty($success)): ?>
  <div class="toast toast--success" id="toast">
    <span class="toast-icon">&
    <span><?= $success ?></span>
  </div>
<?php endif; ?>

  <div class="auth-wrap">
    <div class="auth-card">
      <div class="auth-left">
        <img src="images/uclogo-removebg-preview-removebg-preview.png" alt="University of Cebu" class="auth-uc-logo"/>
        <p class="auth-tagline">Student Portal</p>
        <h2 class="auth-title">Create an account.</h2>
        <p class="auth-sub">Fill in your details to get started with UC CCS.</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert--error">
            <span class="alert-icon">&
            <span>Please fix the highlighted fields before continuing.</span>
          </div>
        <?php endif; ?>

        <form method="POST" action="Register.php" novalidate>

          <div class="field-row">
            <div class="field<?= isset($errors['first_name']) ? ' field--error' : '' ?>">
              <label for="first_name">First Name</label>
              <input type="text" id="first_name" name="first_name" placeholder="Juan"
                value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" autocomplete="given-name"/>
              <?php if (isset($errors['first_name'])): ?>
                <span class="field-msg"><?= htmlspecialchars($errors['first_name']) ?></span>
              <?php endif; ?>
            </div>
            <div class="field">
              <label for="middle_name">Middle Name <span class="field-optional">(optional)</span></label>
              <input type="text" id="middle_name" name="middle_name" placeholder="Santos"
                value="<?= htmlspecialchars($old['middle_name'] ?? '') ?>" autocomplete="additional-name"/>
            </div>
          </div>

          <div class="field<?= isset($errors['last_name']) ? ' field--error' : '' ?>">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" placeholder="dela Cruz"
              value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" autocomplete="family-name"/>
            <?php if (isset($errors['last_name'])): ?>
              <span class="field-msg"><?= htmlspecialchars($errors['last_name']) ?></span>
            <?php endif; ?>
          </div>

          <div class="field<?= isset($errors['student_id']) ? ' field--error' : '' ?>">
            <label for="student_id">Student ID</label>
            <input type="text" id="student_id" name="student_id" placeholder="2025-XXXXX"
              value="<?= htmlspecialchars($old['student_id'] ?? '') ?>"/>
            <?php if (isset($errors['student_id'])): ?>
              <span class="field-msg"><?= htmlspecialchars($errors['student_id']) ?></span>
            <?php endif; ?>
          </div>

          <div class="field-row">
            <div class="field<?= isset($errors['course']) ? ' field--error' : '' ?>">
              <label for="course">Course</label>
              <select id="course" name="course">
                <option value="">-- Select course --</option>
                <?php foreach ($course_options as $val => $label): ?>
                  <option value="<?= $val ?>"<?= ($old['course'] ?? '') === $val ? ' selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['course'])): ?>
                <span class="field-msg"><?= htmlspecialchars($errors['course']) ?></span>
              <?php endif; ?>
            </div>
            <div class="field<?= isset($errors['level']) ? ' field--error' : '' ?>">
              <label for="level">Year Level</label>
              <select id="level" name="level">
                <option value="">-- Select level --</option>
                <?php foreach ($level_options as $opt): ?>
                  <option value="<?= $opt ?>"<?= ($old['level'] ?? '') === $opt ? ' selected' : '' ?>>
                    <?= htmlspecialchars($opt) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['level'])): ?>
                <span class="field-msg"><?= htmlspecialchars($errors['level']) ?></span>
              <?php endif; ?>
            </div>
          </div>

          <div class="field<?= isset($errors['email']) ? ' field--error' : '' ?>">
            <label for="email">UC Email Address</label>
            <input type="email" id="email" name="email" placeholder="you@uc.edu.ph"
              value="<?= htmlspecialchars($old['email'] ?? '') ?>" autocomplete="email"/>
            <?php if (isset($errors['email'])): ?>
              <span class="field-msg"><?= htmlspecialchars($errors['email']) ?></span>
            <?php endif; ?>
          </div>

          <div class="field<?= isset($errors['address']) ? ' field--error' : '' ?>">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" placeholder="Barangay, City, Province"
              value="<?= htmlspecialchars($old['address'] ?? '') ?>" autocomplete="street-address"/>
            <?php if (isset($errors['address'])): ?>
              <span class="field-msg"><?= htmlspecialchars($errors['address']) ?></span>
            <?php endif; ?>
          </div>

          <div class="field-row">
            <div class="field<?= isset($errors['password']) ? ' field--error' : '' ?>">
              <label for="password">Password</label>
              <div class="input-wrap">
                <input type="password" id="password" name="password"
                  placeholder="Min. 8 characters" autocomplete="new-password"/>
                <button type="button" class="pw-toggle" onclick="togglePw('password',this)">&
              </div>
              <?php if (isset($errors['password'])): ?>
                <span class="field-msg"><?= htmlspecialchars($errors['password']) ?></span>
              <?php endif; ?>
            </div>
            <div class="field<?= isset($errors['confirm_password']) ? ' field--error' : '' ?>">
              <label for="confirm_password">Confirm Password</label>
              <div class="input-wrap">
                <input type="password" id="confirm_password" name="confirm_password"
                  placeholder="Repeat password" autocomplete="new-password"/>
                <button type="button" class="pw-toggle" onclick="togglePw('confirm_password',this)">&
              </div>
              <?php if (isset($errors['confirm_password'])): ?>
                <span class="field-msg"><?= htmlspecialchars($errors['confirm_password']) ?></span>
              <?php endif; ?>
            </div>
          </div>

          <div class="pw-strength-wrap" id="pw-strength-wrap" style="display:none">
            <div class="pw-strength-bar">
              <div class="pw-strength-fill" id="pw-strength-fill"></div>
            </div>
            <span class="pw-strength-label" id="pw-strength-label"></span>
          </div>

          <button class="auth-btn" type="submit">Create Account</button>
        </form>

        <p class="auth-switch">Already registered? <a href="Login.php">Sign in here</a></p>
        <a href="Landing.php" class="auth-back">&larr; Back to homepage</a>
      </div>

      <div class="auth-right">
        <img src="images/csmainlogo-removebg-preview-removebg-preview.png" alt="CCS" class="auth-ccs-logo"/>
        <div class="auth-right-name">
          College of Computer Studies
          <small>University of Cebu</small>
        </div>
        <div class="auth-right-rule"></div>
        <p class="auth-right-motto">Be Focused Be Devoted</p>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/footer.php'; ?>

<script>
function togglePw(fieldId, btn) {
  const input = document.getElementById(fieldId);
  if (input.type === 'password') { input.type = 'text';     btn.textContent = '\uD83D\uDE48'; }
  else                           { input.type = 'password'; btn.textContent = '\uD83D\uDC41'; }
}
const pwInput = document.getElementById('password');
const wrap    = document.getElementById('pw-strength-wrap');
const fill    = document.getElementById('pw-strength-fill');
const lbl     = document.getElementById('pw-strength-label');
pwInput.addEventListener('input', () => {
  const v = pwInput.value;
  if (!v) { wrap.style.display = 'none'; return; }
  wrap.style.display = 'flex';
  let score = 0;
  if (v.length >= 8)           score++;
  if (/[A-Z]/.test(v))         score++;
  if (/[0-9]/.test(v))         score++;
  if (/[^A-Za-z0-9]/.test(v))  score++;
  const levels = [
    { pct:'25%', color:'
    { pct:'50%', color:'
    { pct:'75%', color:'
    { pct:'100%',color:'
  ];
  const l = levels[score - 1] || levels[0];
  fill.style.width = l.pct; fill.style.background = l.color;
  lbl.textContent = l.text; lbl.style.color = l.color;
});
const confirmInput = document.getElementById('confirm_password');
confirmInput.addEventListener('input', () => {
  if (!confirmInput.value) { confirmInput.style.borderColor = ''; return; }
  confirmInput.style.borderColor = confirmInput.value === pwInput.value ? '
});
const toast = document.getElementById('toast');
if (toast) setTimeout(() => toast.classList.add('toast--hide'), 4500);
</script>
</body>
</html>