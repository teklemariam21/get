<?php
$pageTitle = 'Contact Us';
require_once 'includes/header.php';

$success  = false;
$error    = '';
$propName = isset($_GET['property']) ? htmlspecialchars($_GET['property']) : '';
$towerPre = isset($_GET['tower'])    ? htmlspecialchars($_GET['tower'])    : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = sanitize($_POST['name']     ?? '');
    $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone    = sanitize($_POST['phone']    ?? '');
    $tower    = sanitize($_POST['tower_interest'] ?? '');
    $message  = sanitize($_POST['message']  ?? '');

    if (!$name || !$email || !$phone || !$message) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $fullMsg = $tower ? "Tower/Unit Interest: $tower\n\n$message" : $message;
        $db->prepare("INSERT INTO inquiries (name,email,phone,message,tower,ip_address) VALUES (:n,:e,:p,:m,:t,:ip)")
           ->execute([':n'=>$name,':e'=>$email,':p'=>$phone,':m'=>$fullMsg,':t'=>$tower,':ip'=>$_SERVER['REMOTE_ADDR']]);
        $success = true;
    }
}
?>

<div class="page-hero">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="index.php">Home</a></li><li class="breadcrumb-item active text-white-50">Contact</li></ol>
    </nav>
    <h1 class="text-white fw-bold">Get in Touch</h1>
    <p style="color:rgba(255,255,255,0.7);margin:0">Our City Gate sales team is ready to help — Mon to Sat, 8AM–6PM.</p>
  </div>
</div>

<div class="container section-py">
  <div class="row g-4">
    <!-- Contact cards -->
    <div class="col-lg-4">
      <div class="row g-3">
        <?php
        $cards = [
          ['bi-telephone-fill','Call Us','<a href="tel:'.preg_replace('/\s+','',$settings['phone_1']??'').'" class="d-block text-muted hover-gold mb-1">'.htmlspecialchars($settings['phone_1']??'').'</a>'.(!empty($settings['phone_2'])?'<a href="tel:'.preg_replace('/\s+','',$settings['phone_2']).'" class="d-block text-muted hover-gold">'.htmlspecialchars($settings['phone_2']).'</a>':'').'<div class="small text-muted mt-2">'.htmlspecialchars($settings['working_hours']??'').'</div>'],
          ['bi-envelope-fill','Email Us','<a href="mailto:'.htmlspecialchars($settings['email']??'').'" class="text-muted hover-gold">'.htmlspecialchars($settings['email']??'').'</a><div class="small text-muted mt-2">We reply within 2 hours</div>'],
          ['bi-whatsapp','WhatsApp','<a href="https://wa.me/251911234567" target="_blank" class="text-muted hover-gold">+251 911 234 567</a><div class="small text-muted mt-2">Instant response</div>'],
          ['bi-geo-alt-fill','Visit Us',htmlspecialchars($settings['address']??'Addis Ababa, Ethiopia').'<div class="small text-muted mt-1">Sales office at the development site</div>'],
        ];
        foreach ($cards as $c): ?>
        <div class="col-12">
          <div class="contact-card">
            <div class="contact-icon"><i class="bi <?= $c[0] ?>"></i></div>
            <h6 class="fw-700 mb-2"><?= $c[1] ?></h6>
            <div><?= $c[2] ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Form -->
    <div class="col-lg-8">
      <div class="form-card">
        <h4 class="fw-bold mb-1" style="font-family:'Playfair Display',serif;color:var(--dark)">Send Us a Message</h4>
        <p class="text-muted small mb-4">Fill in the form and we'll contact you within 2 hours.</p>

        <?php if ($success): ?>
        <div class="alert alert-success alert-auto-dismiss">
          <i class="bi bi-check-circle-fill me-2"></i><strong>Message sent!</strong> Our team will reach you very soon.
        </div>
        <?php elseif ($error): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name *</label>
              <input type="text" name="name" class="form-control" required placeholder="Abebe Kebede">
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number *</label>
              <input type="tel" name="phone" class="form-control" required placeholder="+251 9XX XXX XXX">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Address *</label>
              <input type="email" name="email" class="form-control" required placeholder="you@example.com">
            </div>
            <div class="col-md-6">
              <label class="form-label">Apartment Interest</label>
              <select name="tower_interest" class="form-select">
                <option value="">— Select Tower & Type —</option>
                <optgroup label="City Gate 1 (Wing 1)">
                  <option value="City Gate 1 – 2 Bedroom" <?= $towerPre==='City Gate 1'?'selected':'' ?>>2 Bedroom (115–117m²)</option>
                  <option value="City Gate 1 – 3 Bedroom" <?= ($towerPre==='City Gate 1'&&strpos($propName,'3BR')!==false)?'selected':'' ?>>3 Bedroom (137–144m²)</option>
                </optgroup>
                <optgroup label="City Gate 2 (Wing 2)">
                  <option value="City Gate 2 – 2 Bedroom" <?= $towerPre==='City Gate 2'?'selected':'' ?>>2 Bedroom (115–117m²)</option>
                  <option value="City Gate 2 – 3 Bedroom">3 Bedroom (137–144m²)</option>
                </optgroup>
                <optgroup label="City Gate 3 (Wing 3)">
                  <option value="City Gate 3 – 2 Bedroom" <?= $towerPre==='City Gate 3'?'selected':'' ?>>2 Bedroom (132–149m²)</option>
                  <option value="City Gate 3 – 3 Bedroom">3 Bedroom (159m²)</option>
                </optgroup>
                <option value="Payment Plan Enquiry">Payment Plan Enquiry</option>
                <option value="General Enquiry">General Enquiry</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Message *</label>
              <textarea name="message" class="form-control" rows="5" required
                placeholder="I am interested in viewing a City Gate apartment. Please contact me."><?= $propName ? htmlspecialchars("I'm interested in the $propName and would like to schedule a site visit.") : '' ?></textarea>
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

      <div class="mt-4 rounded-xl overflow-hidden d-flex align-items-center justify-content-center" style="height:260px;background:#e5e7eb;border:1px solid #e5e7eb">
        <div class="text-center text-muted">
          <i class="bi bi-map" style="font-size:3rem;color:#9ca3af"></i>
          <p class="mt-2 mb-0 fw-600">City Gate — Addis Ababa</p>
          <small>Embed Google Maps here</small>
        </div>
      </div>
    </div>
  </div>
</div>

<a href="https://wa.me/251911234567" target="_blank" class="wa-float"><i class="bi bi-whatsapp"></i></a>
<?php require_once 'includes/footer.php'; ?>
