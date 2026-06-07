<?php
$pageTitle = 'Contact Us';
require_once 'includes/header.php';

$success = false;
$error   = '';
$propName = isset($_GET['property']) ? htmlspecialchars($_GET['property']) : '';
$siteName2 = isset($_GET['site']) ? htmlspecialchars($_GET['site']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['name']    ?? '');
    $email   = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone   = sanitize($_POST['phone']   ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $propMsg = sanitize($_POST['property_interest'] ?? '');

    if (!$name || !$email || !$phone || !$message) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $fullMsg = $propMsg ? "Interested in: $propMsg\n\n$message" : $message;
        $ins = $db->prepare("INSERT INTO inquiries (name, email, phone, message, ip_address) VALUES (:n,:e,:p,:m,:ip)");
        $ins->execute([':n'=>$name,':e'=>$email,':p'=>$phone,':m'=>$fullMsg,':ip'=>$_SERVER['REMOTE_ADDR']]);
        $success = true;
    }
}
?>

<!-- Page Hero -->
<div class="page-hero">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active text-white-50">Contact</li>
      </ol>
    </nav>
    <h1 class="text-white fw-bold">Get in Touch</h1>
    <p style="color:rgba(255,255,255,0.7);margin:0">Our sales team is ready to help you find your perfect apartment.</p>
  </div>
</div>

<div class="container section-py">
  <div class="row g-4">
    <!-- Contact Cards -->
    <div class="col-lg-4">
      <div class="row g-3">
        <div class="col-12">
          <div class="contact-card">
            <div class="contact-icon"><i class="bi bi-telephone-fill"></i></div>
            <h6 class="fw-700 mb-2">Call Us</h6>
            <a href="tel:<?= preg_replace('/\s+/','',$settings['phone_1']??'') ?>" class="d-block text-muted text-decoration-none hover-gold mb-1"><?= htmlspecialchars($settings['phone_1']??'') ?></a>
            <?php if (!empty($settings['phone_2'])): ?>
            <a href="tel:<?= preg_replace('/\s+/','',$settings['phone_2']) ?>" class="d-block text-muted text-decoration-none hover-gold"><?= htmlspecialchars($settings['phone_2']) ?></a>
            <?php endif; ?>
            <div class="small text-muted mt-2"><?= htmlspecialchars($settings['working_hours']??'Mon–Sat: 8AM–6PM') ?></div>
          </div>
        </div>
        <div class="col-12">
          <div class="contact-card">
            <div class="contact-icon"><i class="bi bi-envelope-fill"></i></div>
            <h6 class="fw-700 mb-2">Email Us</h6>
            <a href="mailto:<?= htmlspecialchars($settings['email']??'') ?>" class="text-muted text-decoration-none hover-gold">
              <?= htmlspecialchars($settings['email']??'info@getasreality.com') ?>
            </a>
            <div class="small text-muted mt-2">We reply within 2 business hours</div>
          </div>
        </div>
        <div class="col-12">
          <div class="contact-card">
            <div class="contact-icon"><i class="bi bi-whatsapp"></i></div>
            <h6 class="fw-700 mb-2">WhatsApp</h6>
            <a href="https://wa.me/251911234567" target="_blank" class="text-muted text-decoration-none hover-gold">+251 911 234 567</a>
            <div class="small text-muted mt-2">Chat with us instantly</div>
          </div>
        </div>
        <div class="col-12">
          <div class="contact-card">
            <div class="contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
            <h6 class="fw-700 mb-2">Our Office</h6>
            <p class="text-muted small mb-0"><?= htmlspecialchars($settings['address']??'Bole Road, Addis Ababa') ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Form -->
    <div class="col-lg-8">
      <div class="form-card">
        <h4 class="fw-bold mb-1" style="font-family:'Playfair Display',serif;color:var(--dark)">Send Us a Message</h4>
        <p class="text-muted small mb-4">Fill in the form below and we'll get back to you within 2 hours.</p>

        <?php if ($success): ?>
        <div class="alert alert-success alert-auto-dismiss">
          <i class="bi bi-check-circle-fill me-2"></i>
          <strong>Message sent!</strong> Our team will contact you very soon. Thank you!
        </div>
        <?php elseif ($error): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name *</label>
              <input type="text" name="name" class="form-control" placeholder="Abebe Kebede" required
                     value="<?= isset($_POST['name'])?htmlspecialchars($_POST['name']):'' ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number *</label>
              <input type="tel" name="phone" class="form-control" placeholder="+251 9XX XXX XXX" required
                     value="<?= isset($_POST['phone'])?htmlspecialchars($_POST['phone']):'' ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Address *</label>
              <input type="email" name="email" class="form-control" placeholder="you@example.com" required
                     value="<?= isset($_POST['email'])?htmlspecialchars($_POST['email']):'' ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Apartment Interest</label>
              <select name="property_interest" class="form-select">
                <option value="">— Select —</option>
                <optgroup label="Summit 72">
                  <option value="Summit 72 – 1 Bedroom" <?= ($propName && strpos($propName,'Summit')!==false && strpos($propName,'1BR')!==false)?'selected':'' ?>>1 Bedroom (61–65m²)</option>
                  <option value="Summit 72 – 2 Bedroom">2 Bedroom (109–115m²)</option>
                  <option value="Summit 72 – 3 Bedroom">3 Bedroom (144–151m²)</option>
                </optgroup>
                <optgroup label="Kazanchis">
                  <option value="Kazanchis – 1 Bedroom">1 Bedroom (61–65m²)</option>
                  <option value="Kazanchis – 2 Bedroom">2 Bedroom (109–115m²)</option>
                  <option value="Kazanchis – 3 Bedroom">3 Bedroom (144–151m²)</option>
                </optgroup>
                <option value="General Enquiry">General Enquiry</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Message *</label>
              <textarea name="message" class="form-control" rows="5" required
                placeholder="I am interested in viewing an apartment. Please contact me at your earliest convenience."><?= isset($_POST['message'])?htmlspecialchars($_POST['message']):($propName?"I'm interested in the $propName and would like to schedule a site visit.":'') ?></textarea>
            </div>
            <div class="col-12">
              <div class="d-flex gap-3 align-items-center flex-wrap">
                <button type="submit" class="btn btn-gold btn-lg px-5">
                  <i class="bi bi-send me-2"></i>Send Message
                </button>
                <span class="small text-muted"><i class="bi bi-shield-check me-1 text-gold"></i>Your information is safe with us</span>
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- Map placeholder -->
      <div class="mt-4 rounded-xl overflow-hidden" style="height:280px;background:#e5e7eb;display:flex;align-items:center;justify-content:center;border:1px solid #e5e7eb">
        <div class="text-center text-muted">
          <i class="bi bi-map" style="font-size:3rem;color:#9ca3af"></i>
          <p class="mt-2 mb-0">Bole Road, Addis Ababa</p>
          <small>Embed Google Maps here</small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- WhatsApp Float -->
<a href="https://wa.me/251911234567" target="_blank" class="wa-float"><i class="bi bi-whatsapp"></i></a>

<?php require_once 'includes/footer.php'; ?>
