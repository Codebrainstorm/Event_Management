<?php
session_start();
require_once 'config.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = 'Security token validation failed. Please try again.';
    } else {
        // Get form data
        $customer_name = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
        $birthday_person = isset($_POST['birthday_person']) ? trim($_POST['birthday_person']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $event_date = isset($_POST['event_date']) ? trim($_POST['event_date']) : '';
        $theme = isset($_POST['theme']) ? trim($_POST['theme']) : '';
        $guest_count = isset($_POST['guests']) ? intval($_POST['guests']) : 0;
        $special_requests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : '';
        
        // Get party preferences
        $preferences = isset($_POST['preferences']) ? $_POST['preferences'] : [];
        $preferences_text = !empty($preferences) ? 'Party Preferences: ' . implode(', ', $preferences) . '. ' : '';
        
        // Validation
        $errors = [];
        
        if (empty($customer_name)) {
            $errors[] = 'Full Name is required.';
        } elseif (strlen($customer_name) < 2) {
            $errors[] = 'Full Name must be at least 2 characters.';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }
        
        if (empty($phone)) {
            $errors[] = 'Phone Number is required.';
        } elseif (!preg_match('/^[\d\s\-\+\(\)]+$/', $phone) || strlen(preg_replace('/\D/', '', $phone)) < 10) {
            $errors[] = 'Invalid phone number format.';
        }
        
        if (empty($event_date)) {
            $errors[] = 'Event Date is required.';
        } else {
            $event_timestamp = strtotime($event_date);
            $today_timestamp = strtotime(date('Y-m-d'));
            if ($event_timestamp === false || $event_timestamp <= $today_timestamp) {
                $errors[] = 'Event Date must be a future date.';
            }
        }
        
        if ($guest_count < 1 || $guest_count > 500) {
            $errors[] = 'Guest count must be between 1 and 500.';
        }
        
        // Build special requests with all details
        $final_special_requests = '';
        if (!empty($birthday_person)) {
            $final_special_requests .= 'Birthday Person: ' . $birthday_person . '. ';
        }
        if (!empty($theme)) {
            $final_special_requests .= 'Theme: ' . $theme . '. ';
        }
        $final_special_requests .= $preferences_text;
        if (!empty($special_requests)) {
            $final_special_requests .= 'Additional Requests: ' . $special_requests;
        }
        
        if (empty($errors)) {
            // Escape inputs
            $customer_name = mysqli_real_escape_string($connection, $customer_name);
            $email = mysqli_real_escape_string($connection, $email);
            $phone = mysqli_real_escape_string($connection, $phone);
            $event_date = mysqli_real_escape_string($connection, $event_date);
            $final_special_requests = mysqli_real_escape_string($connection, $final_special_requests);
            
            // Insert into database
            $query = "INSERT INTO bookings (event_type, customer_name, email, phone, event_date, guest_count, special_requests) 
                      VALUES ('Birthday', '$customer_name', '$email', '$phone', '$event_date', $guest_count, '$final_special_requests')";
            
            if (mysqli_query($connection, $query)) {
                $success_message = 'Birthday protocol confirmed! We will contact you shortly to establish coordinates.';
                // Clear form
                $_POST = [];
            } else {
                $error_message = 'Database error. Please try again later.';
            }
        } else {
            $error_message = implode(' ', $errors);
        }
    }
}

include 'header.php';
?>

<style>
/* CYBERPUNK/FUTURISTIC PAGE OVERRIDES FOR BIRTHDAY BOOOKING */
body {
    background: #050812;
    color: #e2e8f0;
}

.birthday-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    position: relative;
    padding: 60px 20px 100px;
    background: #050812;
}

.birthday-container::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: 
        linear-gradient(90deg, rgba(5,8,18, 1) 0%, rgba(5,8,18, 0.8) 20%, rgba(5,8,18, 0.8) 80%, rgba(5,8,18, 1) 100%),
        linear-gradient(0deg, rgba(155, 89, 182, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(155, 89, 182, 0.03) 1px, transparent 1px);
    background-size: 100% 100%, 40px 40px, 40px 40px;
    pointer-events: none;
    z-index: 0;
}

.hero-section {
    text-align: center;
    color: white;
    margin-bottom: 50px;
    position: relative;
    z-index: 2;
    animation: slideDown 0.8s ease-out;
}

.hero-section h1 {
    font-size: 4rem;
    margin-bottom: 20px;
    font-weight: 900;
    text-shadow: 0 0 30px rgba(155, 89, 182, 0.6);
    letter-spacing: -1px;
}

.hero-section p {
    font-size: 1.2rem;
    color: #94a3b8;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

.emoji-decoration {
    font-size: 3rem;
    margin: 20px 0;
    animation: bounce 2s infinite;
    filter: drop-shadow(0 0 10px rgba(155, 89, 182, 0.8));
}

.form-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    position: relative;
    z-index: 2;
    animation: fadeIn 0.8s ease-out 0.2s both;
}

.form-container {
    background: rgba(8, 11, 26, 0.8);
    backdrop-filter: blur(12px);
    border-radius: 20px;
    padding: 50px;
    width: 100%;
    max-width: 700px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(155, 89, 182, 0.1);
    border: 1px solid rgba(155, 89, 182, 0.2);
}

.form-title {
    text-align: center;
    margin-bottom: 40px;
}

.form-title h2 {
    font-size: 2.2rem;
    color: white;
    margin-bottom: 10px;
    font-weight: 900;
    text-shadow: 0 0 15px rgba(155, 89, 182, 0.4);
}

.form-title p {
    color: #94a3b8;
    font-size: 1rem;
    letter-spacing: 0.5px;
}

.form-group {
    margin-bottom: 25px;
    position: relative;
}

.form-group-double {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 10px;
    font-weight: 700;
    color: #9b59b6;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    font-size: 1rem;
    font-family: 'Outfit', sans-serif;
    transition: all 0.3s ease;
    background: rgba(5, 8, 18, 0.6);
    color: white;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #9b59b6;
    background: rgba(5, 8, 18, 0.9);
    box-shadow: 0 0 15px rgba(155, 89, 182, 0.2);
    transform: translateY(-2px);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #64748b;
}

.form-group select option { background: #080b1a; color: white; }

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.checkbox-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-top: 15px;
    background: rgba(15, 23, 42, 0.4);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.05);
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkbox-wrapper input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #9b59b6;
}

.checkbox-wrapper label {
    margin: 0;
    cursor: pointer;
    font-weight: 600;
    color: #cbd5e1;
    text-transform: none;
    letter-spacing: 0;
}

.submit-btn {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #9b59b6, #ec4899);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.4s ease;
    margin-top: 20px;
    box-shadow: 0 0 20px rgba(155, 89, 182, 0.4);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

.submit-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 30px rgba(155, 89, 182, 0.6);
}

.submit-btn:active {
    transform: translateY(-1px);
}

.submit-btn.loading {
    opacity: 0.7;
    pointer-events: none;
    background: rgba(155, 89, 182, 0.5);
    box-shadow: none;
}

.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-right: 8px;
}

.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    animation: slideIn 0.4s ease-out;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
    box-shadow: inset 0 0 20px rgba(239, 68, 68, 0.05);
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.3);
    box-shadow: inset 0 0 20px rgba(34, 197, 94, 0.05);
}

.alert-icon { font-size: 1.2rem; flex-shrink: 0; }

.confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    pointer-events: none;
    z-index: 10000;
}

@keyframes slideDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
@keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes fall { to { transform: translateY(100vh) rotate(360deg); opacity: 0; } }

@media (max-width: 768px) {
    .hero-section h1 { font-size: 2.5rem; }
    .hero-section p { font-size: 1rem; }
    .form-container { padding: 40px 25px; }
    .form-title h2 { font-size: 1.8rem; }
    .form-group-double { grid-template-columns: 1fr; }
    .checkbox-group { grid-template-columns: 1fr; }
}

@media (max-width: 480px) {
    .birthday-container { padding: 20px 15px; }
    .form-container { padding: 30px 20px; }
    .form-group { margin-bottom: 20px; }
}
</style>

<div class="birthday-container">
    <div class="hero-section">
        <div class="emoji-decoration">🎂 🎉 🎈</div>
        <h1>Birthday Protocol</h1>
        <p>Initialize your personalized celebration sequence</p>
    </div>

    <div class="form-wrapper">
        <div class="form-container">
            <div class="form-title">
                <h2>✨ Plan Your Event</h2>
                <p>Compile parameters to generate magical network instances</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <span><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="birthday-booking.php" id="birthdayForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-group-double">
                    <div class="form-group">
                        <label for="fullname"><i class="fas fa-user"></i> Primary Contact *</label>
                        <input type="text" id="fullname" name="fullname" required value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" placeholder="Enter Designation">
                    </div>

                    <div class="form-group">
                        <label for="birthday_person"><i class="fas fa-crown"></i> Subject Entity</label>
                        <input type="text" id="birthday_person" name="birthday_person" value="<?php echo isset($_POST['birthday_person']) ? htmlspecialchars($_POST['birthday_person']) : ''; ?>" placeholder="Target Entity Name">
                    </div>
                </div>

                <div class="form-group-double">
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Communication Array *</label>
                        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" placeholder="you@domain.com">
                    </div>

                    <div class="form-group">
                        <label for="phone"><i class="fas fa-mobile-alt"></i> Comm Frequency *</label>
                        <input type="tel" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" placeholder="+1 (555) 123-4567">
                    </div>
                </div>

                <div class="form-group-double">
                    <div class="form-group">
                        <label for="event_date"><i class="fas fa-calendar-alt"></i> Execution Date *</label>
                        <input type="date" id="event_date" name="event_date" required value="<?php echo isset($_POST['event_date']) ? htmlspecialchars($_POST['event_date']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="theme"><i class="fas fa-palette"></i> Aesthetic Directive *</label>
                        <select id="theme" name="theme" required>
                            <option value="">Select Protocol</option>
                            <option value="Kids Birthday (1–12)" <?php echo (isset($_POST['theme']) && $_POST['theme'] === 'Kids Birthday (1–12)') ? 'selected' : ''; ?>>Early Stage (Years 1–12)</option>
                            <option value="Teen Birthday (13–19)" <?php echo (isset($_POST['theme']) && $_POST['theme'] === 'Teen Birthday (13–19)') ? 'selected' : ''; ?>>Mid Stage (Years 13–19)</option>
                            <option value="Adult Birthday (20+)" <?php echo (isset($_POST['theme']) && $_POST['theme'] === 'Adult Birthday (20+)') ? 'selected' : ''; ?>>Advanced Stage (Years 20+)</option>
                            <option value="Milestone Birthday" <?php echo (isset($_POST['theme']) && $_POST['theme'] === 'Milestone Birthday') ? 'selected' : ''; ?>>Milestone Achieved</option>
                            <option value="Surprise Party" <?php echo (isset($_POST['theme']) && $_POST['theme'] === 'Surprise Party') ? 'selected' : ''; ?>>Covert Operation / Surprise</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="guests"><i class="fas fa-users"></i> Entity Count Limit (1-500) *</label>
                    <input type="number" id="guests" name="guests" min="1" max="500" required value="<?php echo isset($_POST['guests']) ? htmlspecialchars($_POST['guests']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-cubes"></i> Additional Parameters</label>
                    <div class="checkbox-group">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="pref_decorations" name="preferences[]" value="Decorations" <?php echo (isset($_POST['preferences']) && in_array('Decorations', $_POST['preferences'])) ? 'checked' : ''; ?>>
                            <label for="pref_decorations">🎨 Spatial Enhancement (Decorations)</label>
                        </div>
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="pref_catering" name="preferences[]" value="Catering" <?php echo (isset($_POST['preferences']) && in_array('Catering', $_POST['preferences'])) ? 'checked' : ''; ?>>
                            <label for="pref_catering">🍽️ Sustenance (Catering)</label>
                        </div>
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="pref_cake" name="preferences[]" value="Custom Cake" <?php echo (isset($_POST['preferences']) && in_array('Custom Cake', $_POST['preferences'])) ? 'checked' : ''; ?>>
                            <label for="pref_cake">🧁 Synthesized Pastry (Cake)</label>
                        </div>
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="pref_entertainment" name="preferences[]" value="Entertainment" <?php echo (isset($_POST['preferences']) && in_array('Entertainment', $_POST['preferences'])) ? 'checked' : ''; ?>>
                            <label for="pref_entertainment">🎭 Audio/Visual Stimuli</label>
                        </div>
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="pref_photography" name="preferences[]" value="Photography" <?php echo (isset($_POST['preferences']) && in_array('Photography', $_POST['preferences'])) ? 'checked' : ''; ?>>
                            <label for="pref_photography">📸 Memory Encoding (Photo)</label>
                        </div>
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="pref_venue" name="preferences[]" value="Venue" <?php echo (isset($_POST['preferences']) && in_array('Venue', $_POST['preferences'])) ? 'checked' : ''; ?>>
                            <label for="pref_venue">🏛️ Secure Location (Venue)</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="special_requests"><i class="fas fa-terminal"></i> Custom Directives</label>
                    <textarea id="special_requests" name="special_requests" placeholder="Input specific sequence parameters here..."><?php echo isset($_POST['special_requests']) ? htmlspecialchars($_POST['special_requests']) : ''; ?></textarea>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <span id="btnText"><i class="fas fa-satellite-dish"></i> Execute Booking Protocol</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Function to format phone number
    function formatPhoneNumber(value) {
        const numbers = value.replace(/\D/g, '');
        if (numbers.length === 0) return '';
        if (numbers.length <= 3) return numbers;
        if (numbers.length <= 6) return `(${numbers.slice(0, 3)}) ${numbers.slice(3)}`;
        return `(${numbers.slice(0, 3)}) ${numbers.slice(3, 6)}-${numbers.slice(6, 10)}`;
    }

    // Phone number input formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = formatPhoneNumber(this.value);
        });
    }

    // Set minimum date to today
    const eventDateInput = document.getElementById('event_date');
    if (eventDateInput) {
        const today = new Date().toISOString().split('T')[0];
        eventDateInput.min = today;
    }

    // Form submission
    const birthdayForm = document.getElementById('birthdayForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');

    if (birthdayForm) {
        birthdayForm.addEventListener('submit', function(e) {
            submitBtn.classList.add('loading');
            btnText.innerHTML = '<span class="spinner"></span>Transmitting...';
        });
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'slideIn 0.4s ease-out reverse';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 400);
        }, 5000);
    });

    // Confetti animation
    function createConfetti() {
        const colors = ['#00f0ff', '#9b59b6', '#f59e0b', '#ec4899', '#22c55e'];
        const confettiCount = 100;

        for (let i = 0; i < confettiCount; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animation = `fall ${2 + Math.random() * 1}s linear`;
            confetti.style.animationDelay = Math.random() * 0.2 + 's';
            confetti.style.boxShadow = `0 0 10px ${confetti.style.backgroundColor}`;
            document.body.appendChild(confetti);

            setTimeout(() => {
                confetti.remove();
            }, 3000);
        }
    }

    // Trigger confetti if success message exists
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        createConfetti();
        setTimeout(() => {
            birthdayForm.reset();
        }, 3000);
    }

    // Guest count validation
    const guestInput = document.getElementById('guests');
    if (guestInput) {
        guestInput.addEventListener('change', function() {
            const value = parseInt(this.value);
            if (value < 1) this.value = 1;
            if (value > 500) this.value = 500;
        });
    }
</script>

<?php include 'footer.php'; ?>
