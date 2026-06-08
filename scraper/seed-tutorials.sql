-- ============================================================
-- Hitechcomputer – Seed Tutorial Posts
-- Manually curated tutorial content for immediate use.
-- Run: mysql -u root hitechcomputer < scraper/seed-tutorials.sql
-- ============================================================

USE hitechcomputer;

INSERT IGNORE INTO posts (title, slug, excerpt, content, category, tags, status, featured, created_at) VALUES

-- ── TUTORIALS ──
(
  'How to Replace a Broken iPhone Screen Step by Step',
  'how-to-replace-broken-iphone-screen',
  'Complete step-by-step guide to replacing a cracked or broken iPhone screen with the right tools and technique.',
  '<h2>Tools You Need</h2>
<ul>
<li>Pentalobe screwdriver (P2)</li>
<li>Phillips #000 screwdriver</li>
<li>Suction cup handle</li>
<li>Plastic spudger / pry tool</li>
<li>Tweezers</li>
<li>Replacement LCD/OLED screen assembly</li>
</ul>
<h2>Step 1: Power Off the Phone</h2>
<p>Always power off before opening any device. Press and hold the power button, then slide to power off. Never work on a powered device.</p>
<h2>Step 2: Remove Bottom Pentalobe Screws</h2>
<p>Remove the two pentalobe screws on either side of the Lightning/USB-C connector at the bottom of the phone. Keep them in a small container.</p>
<h2>Step 3: Apply the Suction Cup</h2>
<p>Place the suction cup just above the home button (or lower portion on Face ID models). Gently but firmly pull upward while using a pry tool at the gap between the screen and frame. Work carefully — the screen connects via cables on one side.</p>
<h2>Step 4: Open Like a Book</h2>
<p>Once the bottom lifts, tilt the screen up from the bottom — do NOT pull it away from the top. The display cables run along the top right area and will snap if pulled too far.</p>
<h2>Step 5: Disconnect the Screen Cables</h2>
<p>Remove the metal bracket covering the display connectors (2–3 screws). Use a spudger to pop off each ribbon cable connector. There are typically 3 connectors: LCD/OLED, digitizer, and front camera/earpiece.</p>
<h2>Step 6: Remove Additional Components</h2>
<p>Transfer these from the old screen to the new one:</p>
<ul>
<li>Home button (keeps your fingerprint data)</li>
<li>Earpiece speaker</li>
<li>Front camera module</li>
<li>Metal shield bracket</li>
</ul>
<h2>Step 7: Install New Screen</h2>
<p>Connect the new screen cables in reverse order. Press each connector firmly until you feel a click. Replace the bracket and screws.</p>
<h2>Step 8: Test Before Closing</h2>
<p>Power on the phone before pressing the screen fully into the frame. Test touch, display brightness, home button, and front camera. If everything works, press the screen into the frame firmly along all edges.</p>
<h2>Step 9: Replace Bottom Screws</h2>
<p>Reinstall the two pentalobe screws at the bottom. Done!</p>
<h2>Common Mistakes to Avoid</h2>
<ul>
<li>Ripping cables by opening from the wrong side</li>
<li>Forgetting to transfer the home button (Face ID will work but Touch ID will be lost)</li>
<li>Over-tightening tiny screws (strip the threads)</li>
<li>Not testing before fully sealing</li>
</ul>',
  'tutorial', 'iPhone,screen repair,LCD,display replacement,mobile phone', 'published', 1,
  DATE_SUB(NOW(), INTERVAL 5 DAY)
),

(
  'Mobile Phone Water Damage Repair: Complete Recovery Guide',
  'mobile-phone-water-damage-repair',
  'Learn how to recover a water-damaged mobile phone using professional techniques including ultrasonic cleaning and component inspection.',
  '<h2>First Response (Do This Immediately)</h2>
<ol>
<li><strong>Power off immediately</strong> — do not press power to check if it works</li>
<li>Remove the SIM card and memory card</li>
<li>If removable: take out the battery</li>
<li>Do NOT shake the phone — this spreads water further</li>
<li>Do NOT use a hair dryer on high heat</li>
<li>Do NOT put in rice — this is a myth and wastes time</li>
</ol>
<h2>Professional Repair Process</h2>
<h3>1. Full Disassembly</h3>
<p>Open the phone completely. Remove the motherboard, battery, camera modules, speakers, and all flex cables. Water hides under shields and under components.</p>
<h3>2. Isopropyl Alcohol (IPA) Bath</h3>
<p>Submerge the motherboard in 99% isopropyl alcohol. Use a soft toothbrush to gently scrub all areas. IPA displaces water and evaporates cleanly without leaving residue.</p>
<h3>3. Ultrasonic Cleaning (Professional)</h3>
<p>Place the board in an ultrasonic cleaner with IPA or ultrasonic cleaning solution. Run for 5–10 minutes at 40kHz. This dislodges corrosion from under BGA chips where brushes cannot reach.</p>
<h3>4. Inspect Under Microscope</h3>
<p>Look for green or white corrosion deposits, especially around connectors and around the charging circuit, NAND chip, and CPU area.</p>
<h3>5. Remove Corrosion</h3>
<p>Use a fiberglass scratch pen or brass bristle brush to carefully remove corrosion. Reapply IPA while scrubbing.</p>
<h3>6. Hot Air Dry</h3>
<p>Use a hot air station at 60°C to evaporate remaining IPA. Do not exceed 80°C.</p>
<h3>7. Visual Inspection & Component Testing</h3>
<p>Check all power rails with a DC power supply set to the correct battery voltage (3.7V–4.3V). Monitor current draw: if it shorts (pulls >1A immediately), there is a shorted component.</p>
<h3>8. Reassemble and Test</h3>
<p>Reassemble and test all functions: display, touch, cameras, speakers, microphone, charging, cellular, WiFi.</p>
<h2>Success Rate</h2>
<p>If treated within 24 hours: ~80% success rate. After 48+ hours with corrosion: ~40%. After the phone was powered on while wet: ~30%.</p>
<h2>Tools Required</h2>
<ul>
<li>99% Isopropyl Alcohol</li>
<li>Ultrasonic Cleaner</li>
<li>DC Bench Power Supply</li>
<li>Stereo Microscope (10x–40x)</li>
<li>Hot Air Rework Station</li>
<li>Fiberglass scratch pen</li>
</ul>',
  'tutorial', 'water damage,repair,motherboard,IPA,ultrasonic cleaner', 'published', 1,
  DATE_SUB(NOW(), INTERVAL 10 DAY)
),

(
  'Samsung Galaxy Charging Problem: Diagnosis and Repair',
  'samsung-galaxy-charging-problem-repair',
  'Step-by-step diagnosis and repair guide for Samsung Galaxy phones that are not charging, charging slowly, or showing no charge.',
  '<h2>Common Causes of Charging Problems</h2>
<ol>
<li>Dirty or damaged charging port</li>
<li>Faulty charging cable or adapter</li>
<li>Software/firmware issue</li>
<li>Damaged charging IC chip</li>
<li>Dead or swollen battery</li>
<li>Broken charging port flex cable</li>
</ol>
<h2>Diagnosis Steps</h2>
<h3>Step 1: Test with Different Cable and Adapter</h3>
<p>Always start with the simplest fix. Try a known-working cable and charger. 30% of charging problems are actually caused by faulty accessories.</p>
<h3>Step 2: Clean the Charging Port</h3>
<p>Use a SIM eject tool or toothpick to gently remove lint and debris from the USB-C port. Hold the phone with the port facing down. Use compressed air to blow out loose debris. Never use metal tools aggressively.</p>
<h3>Step 3: Boot into Recovery Mode</h3>
<p>Power off → hold Volume Up + Power (or Bixby + Volume Up + Power on older models) → check if it charges in this mode. If yes, the issue is software-related.</p>
<h3>Step 4: Connect DC Power Supply</h3>
<p>Set supply to 4.0V. Connect to battery terminals or the battery connector. If the phone boots, the battery is dead/defective. If not, the issue is on the board.</p>
<h3>Step 5: Check Charging Port with Multimeter</h3>
<p>Set to DC voltage. With a working charger connected, probe the VBUS pin of the USB-C port (pin 4 on the standard USB-C layout) — should read 5V. No voltage = faulty port or cable.</p>
<h3>Step 6: Inspect Charging IC</h3>
<p>Under microscope, inspect the charging IC (often labeled SM5720, MAX77705, or similar on Samsung boards). Look for cracked solder joints, burns, or lifted pins.</p>
<h2>Repair Procedures</h2>
<h3>Charging Port Replacement</h3>
<p>Remove the back cover, disconnect the battery, unscrew the charging port flex cable, replace with a new genuine part. Cost: 150–300 ETB.</p>
<h3>Battery Replacement</h3>
<p>Heat the back cover at 60–70°C, use a suction cup and plastic cards to separate the adhesive. Replace battery with OEM-spec part. Cost: 400–800 ETB depending on model.</p>
<h3>Charging IC Replacement (Advanced)</h3>
<p>Requires hot air rework station and BGA soldering skills. Remove old IC, clean pads, reball with new solder, place new IC, reflow at 220–240°C. Test immediately after cooling.</p>',
  'tutorial', 'Samsung,charging,USB-C,charging port,battery,repair', 'published', 0,
  DATE_SUB(NOW(), INTERVAL 15 DAY)
),

(
  'How to Install and Configure a CCTV Camera System',
  'how-to-install-configure-cctv-system',
  'Complete guide to planning, installing, and configuring a CCTV security camera system for home or office use.',
  '<h2>System Planning</h2>
<h3>Identify Coverage Areas</h3>
<p>Walk around the property and identify key areas: entrances, driveways, parking areas, cash registers, hallways. Draw a simple floor plan and mark camera positions.</p>
<h3>Choose Camera Type</h3>
<ul>
<li><strong>Bullet cameras:</strong> Long-range outdoor use, fixed direction</li>
<li><strong>Dome cameras:</strong> Indoor use, vandal-resistant</li>
<li><strong>PTZ cameras:</strong> Remote pan/tilt/zoom control</li>
<li><strong>IP cameras:</strong> Network-based, higher resolution (2MP–8MP)</li>
<li><strong>Analog cameras:</strong> Use coaxial cable, cost-effective for small systems</li>
</ul>
<h3>Analog vs. IP Systems</h3>
<p><strong>Analog (AHD/TVI/CVI):</strong> Uses RG59 coaxial cable + DVR. Good for budget installs.<br>
<strong>IP (PoE):</strong> Uses CAT5e/CAT6 cable + NVR or PoE switch. Better image quality, easier to expand.</p>
<h2>Cable Installation</h2>
<h3>Running Coaxial Cable (Analog)</h3>
<p>Use RG59 with pre-made BNC + DC power connectors, or RG59/U with separate 18/2 power cable. Drill through walls using a long flexible drill bit. Leave 30cm slack at each camera end.</p>
<h3>Running CAT6 Cable (IP)</h3>
<p>Route cable through conduits for outdoor runs. Max run length: 100 meters per PoE segment. Use proper RJ45 crimp connectors wired to T568B standard.</p>
<h2>Camera Installation</h2>
<ol>
<li>Mount the back plate/bracket at the chosen location</li>
<li>Thread cables through the mounting bracket</li>
<li>Connect camera to cable (BNC for analog, RJ45 for IP)</li>
<li>Adjust camera angle — aim at entry points at face height (1.5–2m from ground)</li>
<li>Secure with screws and apply silicone sealant around outdoor penetrations</li>
</ol>
<h2>DVR/NVR Configuration</h2>
<h3>Initial Setup</h3>
<ol>
<li>Connect monitor via HDMI/VGA</li>
<li>Connect mouse to USB port</li>
<li>Power on and complete setup wizard</li>
<li>Set date and time accurately</li>
<li>Set recording schedule (continuous or motion-triggered)</li>
</ol>
<h3>Recording Settings</h3>
<p>For a 4-camera system with 1TB hard drive: Set 15fps at 1080P for 15–20 days of continuous recording. Enable motion detection to extend storage life to 30+ days.</p>
<h2>Remote Access Setup</h2>
<ol>
<li>Connect DVR/NVR to router via LAN cable</li>
<li>Assign a static IP to the DVR in router settings</li>
<li>Enable port forwarding (typically port 80 and 8000 for Hikvision/Dahua)</li>
<li>Sign up for DDNS if you do not have a static public IP</li>
<li>Install manufacturer app (iVMS-4500, gDMSS, XMEye) on smartphone</li>
<li>Add device using DDNS hostname or public IP + port</li>
</ol>
<h2>Maintenance Tips</h2>
<ul>
<li>Clean camera lenses monthly with microfiber cloth</li>
<li>Check hard drive health every 6 months (DVR menu > Storage)</li>
<li>Update firmware annually</li>
<li>Verify recording is actually working — check playback weekly</li>
</ul>',
  'tutorial', 'CCTV,security camera,DVR,NVR,installation,IP camera', 'published', 1,
  DATE_SUB(NOW(), INTERVAL 20 DAY)
),

(
  'Laptop Not Turning On: Complete Diagnosis Guide',
  'laptop-not-turning-on-diagnosis',
  'Systematic approach to diagnosing and fixing a laptop that does not power on, from simple checks to board-level repair.',
  '<h2>Step 1: Basic Power Checks</h2>
<ul>
<li>Try a different power adapter (same voltage/amperage)</li>
<li>Remove the battery and try AC power only</li>
<li>Check the power adapter output with a multimeter (should match rated voltage ±0.5V)</li>
<li>Inspect the DC jack for wobbling or broken connections</li>
</ul>
<h2>Step 2: Hard Reset</h2>
<p>Remove AC adapter and battery. Hold the power button for 30 seconds to discharge capacitors. Reconnect AC only and try to power on. This fixes many "dead" laptops.</p>
<h2>Step 3: External Display Test</h2>
<p>Connect an external monitor via HDMI. If the laptop powers on but shows nothing on its screen, the LCD backlight, inverter, or display cable may be faulty — the laptop itself is working.</p>
<h2>Step 4: RAM Reseating</h2>
<p>Open the bottom panel. Remove RAM sticks, clean contacts with an eraser, and reseat. Try one stick at a time in each slot. Faulty RAM often prevents POST (Power-On Self-Test) from completing.</p>
<h2>Step 5: Check Power LED and Fan</h2>
<p>When pressing power, observe: Does any LED light up? Does the fan spin? Does the hard drive activity light flash?<br>
- <strong>No response at all:</strong> Power circuit fault on motherboard or DC jack<br>
- <strong>Fan spins, then stops:</strong> Thermal shutdown or POST failure<br>
- <strong>Runs but no display:</strong> GPU fault or display cable</p>
<h2>Step 6: DC Jack Inspection</h2>
<p>With a multimeter on DC voltage, probe the DC jack center pin with adapter connected. Should read adapter voltage (19V, 20V, or 12V depending on model). No voltage = broken jack or fuse.</p>
<h2>Step 7: Check Main Fuses</h2>
<p>Laptops have small surface-mount fuses near the DC jack and battery connector. Test with multimeter in continuity mode. A blown fuse reads OL (open). Replace with identical-rated component.</p>
<h2>Step 8: BIOS/CMOS Reset</h2>
<p>Locate the CMOS battery (small coin cell on motherboard) or CMOS reset jumper. Remove battery for 5 minutes or short the jumper. This resets BIOS to defaults and may fix boot locks.</p>
<h2>Step 9: Board-Level Repair</h2>
<p>If none of the above work, the fault is on the motherboard. Common failures:</p>
<ul>
<li><strong>EC chip (Embedded Controller):</strong> Controls power sequencing</li>
<li><strong>MOSFET failure:</strong> Short on power rail</li>
<li><strong>Capacitor failure:</strong> Bulged or shorted cap</li>
<li><strong>GPU failure:</strong> Often requires reballing or board replacement</li>
</ul>
<p>These require schematics, board view software (like ZXW or Boardview), and a DC power supply to trace power rails.</p>',
  'tutorial', 'laptop,power,repair,motherboard,not turning on', 'published', 0,
  DATE_SUB(NOW(), INTERVAL 25 DAY)
),

-- ── TIPS ──
(
  'Top 10 Tools Every Mobile Phone Technician Must Have',
  'top-10-tools-mobile-phone-technician',
  'Essential tools you need to set up a professional mobile phone repair workshop from scratch.',
  '<p>Setting up a professional mobile repair workshop requires the right tools. Here are the 10 must-haves:</p>
<h3>1. Hot Air Rework Station</h3>
<p>Used for: Removing and replacing BGA chips, heating adhesive to open screens, desoldering components.<br>
<strong>Recommended:</strong> Quick 861DW or Atten AT858D. Budget: 2,500–5,000 ETB.</p>
<h3>2. DC Bench Power Supply</h3>
<p>Lets you power a phone without a battery to test functions and identify short circuits by monitoring current draw.<br>
<strong>Recommended:</strong> Any 0–15V 3A adjustable supply. Budget: 1,500–3,000 ETB.</p>
<h3>3. Digital Multimeter</h3>
<p>For measuring voltage, resistance, continuity, and diode values. Essential for every step of diagnosis.<br>
<strong>Recommended:</strong> Fluke 101 or Victor VC890D. Budget: 500–2,000 ETB.</p>
<h3>4. Stereo Microscope</h3>
<p>For inspecting tiny solder joints, identifying burnt components, and performing micro-soldering.<br>
<strong>Recommended:</strong> Trinocular 7X–45X with ring LED light. Budget: 4,000–10,000 ETB.</p>
<h3>5. Precision Screwdriver Set</h3>
<p>Includes Pentalobe (P2, P5), Torx (T2, T3, T4, T5), Phillips (#000), and Flathead sizes. Essential for opening every brand of phone.</p>
<h3>6. Ultrasonic Cleaner</h3>
<p>Cleans motherboards after water damage using high-frequency vibrations in IPA. Gets under chips where brushes cannot reach.<br>
Budget: 2,000–4,000 ETB for a 500ml unit.</p>
<h3>7. Soldering Iron with Fine Tips</h3>
<p>For through-hole work and drag-soldering. Pair with 60/40 or 63/37 rosin-core solder 0.3mm diameter.<br>
<strong>Recommended:</strong> Hakko FX-888D clone or Quick 203H.</p>
<h3>8. Isopropyl Alcohol (99%) + Brushes</h3>
<p>For cleaning flux residue, water damage, and contact cleaning. Always use 99% — lower concentrations leave water residue. Budget: 200 ETB/liter.</p>
<h3>9. Phone Opening Kit</h3>
<p>Includes suction cups, plastic spudgers, metal pry bars, tweezers, and opening picks. Keep plastic tools for screens and metal for housing work.</p>
<h3>10. BGA Reballing Kit</h3>
<p>Includes reballing stencils, solder balls (0.3mm–0.6mm), flux paste, and stencil holder. Required for CPU/GPU/RAM chip replacement on modern phones.</p>',
  'tip', 'tools,workshop,equipment,technician setup', 'published', 1,
  DATE_SUB(NOW(), INTERVAL 3 DAY)
),

(
  '5 Quick Tips to Diagnose a Smartphone Faster',
  '5-quick-tips-diagnose-smartphone-faster',
  'Professional diagnosis tricks that experienced technicians use to identify phone faults quickly and accurately.',
  '<h3>Tip 1: Use a DC Power Supply First</h3>
<p>Before even opening the phone, connect it to a DC power supply set to 3.9V. Watch the current meter:</p>
<ul>
<li><strong>0mA:</strong> Open circuit — check charging port, battery connector, fuse</li>
<li><strong>10–100mA (rising slowly):</strong> Normal boot sequence</li>
<li><strong>400mA+ immediately (no display):</strong> Short circuit on board</li>
<li><strong>Normal boot current then drops:</strong> Software issue</li>
</ul>
<h3>Tip 2: Listen to the Phone</h3>
<p>While holding power, listen: Do you hear a startup sound? Can you feel vibration? If the phone responds audibly or via vibration but the screen is dark, the problem is the display — not the board. This saves significant diagnosis time.</p>
<h3>Tip 3: Use iTunes / Finder (iPhone)</h3>
<p>For iPhones, connect to a computer. If iTunes/Finder detects the phone (even in recovery mode), the baseband and CPU are functional. This narrows your diagnosis significantly.</p>
<h3>Tip 4: Check Thermal Signature</h3>
<p>After powering on for 30 seconds, touch the back of the board near different chips. Abnormal heat (you cannot keep your finger on it) indicates a shorted chip. The hottest chip is your prime suspect.</p>
<h3>Tip 5: Swap Known-Good Components</h3>
<p>Keep a "parts phone" — a donor phone of the same model — in your workshop. Swap the screen, battery, or charging port from the known-good phone to quickly confirm whether the fault is in the component or the motherboard. This is the fastest elimination method.</p>',
  'tip', 'diagnosis,tips,tricks,DC power supply,troubleshooting', 'published', 0,
  DATE_SUB(NOW(), INTERVAL 7 DAY)
),

-- ── TRICKS ──
(
  'iPhone Bypass iCloud Activation Lock – What Technicians Need to Know',
  'icloud-activation-lock-technician-guide',
  'Professional technician guide to understanding iCloud Activation Lock and legitimate options for customers who are locked out of their devices.',
  '<p class="alert-glass" style="padding:12px;border-left:3px solid #ff6b35">⚠️ This guide is for legitimate repair technicians helping customers who own their devices. Bypassing iCloud lock on stolen devices is illegal.</p>
<h2>What is Activation Lock?</h2>
<p>iCloud Activation Lock is Apple''s anti-theft feature. When Find My iPhone is enabled, the Apple ID is linked to the device hardware. After a factory reset, the device requires the original Apple ID credentials to activate.</p>
<h2>Legitimate Solutions for Locked Devices</h2>
<h3>Solution 1: Customer Remembers Their Apple ID</h3>
<p>This is the only full solution. Have the customer sign in at appleid.apple.com to reset their password. Then enter credentials on the device. This removes the lock completely.</p>
<h3>Solution 2: Proof of Purchase</h3>
<p>Apple can remove Activation Lock with proof of purchase (original receipt with IMEI). Customer contacts Apple Support with proof of ownership. Resolution: 3–10 business days.</p>
<h3>Solution 3: Previous Owner Removes Lock</h3>
<p>If the customer bought a second-hand device: contact the previous owner and ask them to remove the device from their account at icloud.com/find. This is free and immediate.</p>
<h3>Solution 4: Apple Authorized Service</h3>
<p>Take the device to an Apple Authorized Service Provider with proof of ownership. They can escalate to Apple for lock removal.</p>
<h2>What Technicians Should Tell Customers</h2>
<ol>
<li>Always ask for proof of purchase before taking in a locked iPhone</li>
<li>Any "unlock service" claiming to permanently unlock Activation Lock via IMEI is a scam</li>
<li>Partial bypasses (DNS bypass) are temporary and do not restore full phone functionality</li>
<li>Hardware solutions destroy the device value and are risky</li>
</ol>
<h2>Prevention Advice for Customers</h2>
<ul>
<li>Always sign out of iCloud before selling or giving a phone</li>
<li>Write down Apple ID credentials and keep them safe</li>
<li>Keep original purchase receipt</li>
</ul>',
  'trick', 'iCloud,activation lock,iPhone,Apple ID', 'published', 0,
  DATE_SUB(NOW(), INTERVAL 12 DAY)
),

(
  'How to Flash Samsung Firmware Using Odin – Complete Guide',
  'how-to-flash-samsung-firmware-odin',
  'Step-by-step guide to flashing official Samsung firmware using Odin to fix bootloops, software issues, and factory reset problems.',
  '<h2>What is Odin?</h2>
<p>Odin is Samsung''s official firmware flashing tool (leaked internally, now widely used by technicians). It flashes official Samsung firmware files (.tar, .tar.md5) to Samsung devices via USB in Download Mode.</p>
<h2>When to Use Odin</h2>
<ul>
<li>Phone stuck in bootloop</li>
<li>Soft-bricked after failed update</li>
<li>Need to downgrade/upgrade firmware</li>
<li>Fixing "System UI has stopped" loop</li>
<li>Restoring to stock after custom ROM</li>
</ul>
<h2>What You Need</h2>
<ul>
<li>Windows PC (Odin does not run on Mac/Linux natively)</li>
<li>Odin3 v3.14.4 or latest version</li>
<li>Samsung USB Drivers (install from Samsung website)</li>
<li>Original Samsung firmware for your exact model + region (from samfw.com or sammobile.com)</li>
<li>USB cable (original Samsung preferred)</li>
</ul>
<h2>Step-by-Step Process</h2>
<h3>Step 1: Download Correct Firmware</h3>
<p>Find your exact model number (Settings > About Phone > Model Number) and CSC code (region code). Download the matching firmware. It will be a large ZIP file (3–6GB).</p>
<h3>Step 2: Extract Firmware</h3>
<p>Extract the downloaded ZIP. You will find 4–5 files with extensions .tar.md5:<br>
AP_ (main firmware), BL_ (bootloader), CP_ (modem), CSC_ (region settings), HOME_CSC_ (keeps data)</p>
<h3>Step 3: Enter Download Mode</h3>
<p>Power off phone → Volume Down + Bixby + Power (older models) or Volume Down + Volume Up while connecting USB. Press Volume Up to confirm warning. Screen turns blue/green.</p>
<h3>Step 4: Configure Odin</h3>
<ul>
<li>Open Odin as Administrator</li>
<li>Click BL → select BL_ file</li>
<li>Click AP → select AP_ file</li>
<li>Click CP → select CP_ file</li>
<li>Click CSC → select HOME_CSC_ (keeps data) or CSC_ (wipes data)</li>
<li>In Options tab: ensure "Auto Reboot" is checked, uncheck "Re-Partition"</li>
</ul>
<h3>Step 5: Connect and Flash</h3>
<p>Connect phone via USB. Odin should show "Added!!" in the log box and highlight a COM port. Click START. The process takes 5–15 minutes. Do NOT disconnect during flashing.</p>
<h3>Step 6: Verify Success</h3>
<p>Odin shows "PASS!" in green. Phone reboots automatically. First boot after flash takes 3–7 minutes — do not force restart.</p>
<h2>Troubleshooting Odin</h2>
<ul>
<li><strong>Phone not detected:</strong> Reinstall Samsung USB drivers, try different USB port/cable</li>
<li><strong>FAIL! after flashing:</strong> Wrong firmware file for model/region</li>
<li><strong>Stuck at SYSTEM OFFICIAL:</strong> Wait longer — this step can take up to 5 minutes</li>
</ul>',
  'trick', 'Samsung,Odin,firmware,flash,repair,software', 'published', 1,
  DATE_SUB(NOW(), INTERVAL 18 DAY)
),

(
  'Fix Common Printer Problems: Offline, Paper Jam & Print Quality Issues',
  'fix-common-printer-problems',
  'Practical solutions for the most common office printer problems including offline errors, paper jams, and poor print quality.',
  '<h2>Problem 1: Printer Shows Offline</h2>
<h3>Solution A: Set as Default Printer</h3>
<p>Go to Control Panel > Devices and Printers > Right-click your printer > Set as Default Printer. Then right-click again > See what''s printing > Printer menu > Uncheck "Use Printer Offline".</p>
<h3>Solution B: Restart Print Spooler</h3>
<ol>
<li>Press Windows + R, type <code>services.msc</code></li>
<li>Find "Print Spooler" → Right-click → Stop</li>
<li>Navigate to <code>C:\Windows\System32\spool\PRINTERS</code></li>
<li>Delete all files inside (not the folder itself)</li>
<li>Go back to Services → Start Print Spooler</li>
</ol>
<h3>Solution C: Reinstall Driver</h3>
<p>Download the latest driver from the printer manufacturer''s website. Uninstall the current driver from Device Manager first, then install the new one.</p>
<h2>Problem 2: Paper Jam</h2>
<h3>Safe Removal Steps</h3>
<ol>
<li>Power off the printer before removing jammed paper</li>
<li>Open all access doors/panels</li>
<li>Gently pull paper in the direction of travel — never against the paper path</li>
<li>Check the roller area — small torn pieces cause repeated jams</li>
<li>Check the fuser area on laser printers (it''s hot — wear gloves)</li>
<li>Close all panels and power on — run a test page</li>
</ol>
<h3>Prevent Future Jams</h3>
<ul>
<li>Fan paper before loading to separate sheets</li>
<li>Do not overfill the paper tray (stay below the fill line)</li>
<li>Use recommended paper weight for your printer</li>
<li>Keep paper dry — humid paper jams frequently</li>
<li>Clean rollers monthly with a damp (not wet) cloth</li>
</ul>
<h2>Problem 3: Poor Print Quality</h2>
<h3>Inkjet: Streaky or Missing Colors</h3>
<ol>
<li>Run the printer''s built-in Head Cleaning utility (usually in Maintenance menu)</li>
<li>Print a nozzle check pattern to identify blocked nozzles</li>
<li>If colors are still missing after 3 cleaning cycles, replace the ink cartridge</li>
<li>For stubborn clogs: remove the cartridge and dab the nozzle plate gently on a damp paper towel</li>
</ol>
<h3>Laser: Faded, Streaky, or Ghost Images</h3>
<ul>
<li><strong>Faded print:</strong> Shake the toner cartridge gently side to side (redistributes toner). Replace if still faded.</li>
<li><strong>Black vertical lines:</strong> Dirty drum or damaged drum blade — replace drum unit</li>
<li><strong>Ghost image (previous page showing faintly):</strong> Drum unit needs replacement</li>
<li><strong>Toner not fusing (smears when touched):</strong> Fuser unit is failing — replace</li>
</ul>
<h2>General Maintenance Schedule</h2>
<ul>
<li>Monthly: Clean exterior, wipe feed rollers</li>
<li>Every 6 months: Clean interior dust with compressed air</li>
<li>Annually: Replace drum unit (laser), clean fuser rollers</li>
</ul>',
  'tutorial', 'printer,office machine,repair,offline,paper jam,maintenance', 'published', 0,
  DATE_SUB(NOW(), INTERVAL 30 DAY)
);
