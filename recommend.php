<?php
require_once 'config.php';

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");
$pricingOptions = ['Free', 'Freemium', 'Paid'];

$pricingMeta = [
  'Free'     => ['desc' => 'Free forever, no credit card required',      'icon' => 'layers'],
  'Freemium' => ['desc' => 'Free tier with premium upgrades',            'icon' => 'shield'],
  'Paid'     => ['desc' => 'Professional paid solutions',                'icon' => 'card'],
];

$quickPrompts = ['Content creation', 'Code automation', 'Data processing', 'Customer support', 'Design and creative', 'Research and analysis'];

$results = null;
$hasResults = false;
$selectedCatId = 0;
$selectedPricing = '';
$useCaseInput = '';

$isSearch = ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['category_id']))
         || ($_SERVER['REQUEST_METHOD'] === 'GET'  && !empty($_GET['category_id']));

if ($isSearch) {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedCatId   = (int)$_POST['category_id'];
    $selectedPricing = $_POST['pricing'] ?? '';
    $useCaseInput    = trim($_POST['use_case'] ?? '');
  } else {
    $selectedCatId   = (int)$_GET['category_id'];
    $selectedPricing = trim($_GET['pricing'] ?? '');
  }

  $where  = [];
  $params = [];
  $types  = '';

  if ($selectedCatId > 0) {
    $where[]  = 't.category_id = ?';
    $params[] = $selectedCatId;
    $types   .= 'i';
  }
  if ($selectedPricing !== '') {
    $where[]  = 't.pricing = ?';
    $params[] = $selectedPricing;
    $types   .= 's';
  }

  $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

  $sql = "SELECT t.*, c.category_name, c.slug AS cat_slug
          FROM ai_tools t
          LEFT JOIN categories c ON t.category_id = c.id
          $whereSql
          ORDER BY t.rating DESC, t.created_at DESC";

  $stmt = $conn->prepare($sql);
  if ($params) {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $results = $stmt->get_result();
  $hasResults = $results && $results->num_rows > 0;
}

include 'header.php';
?>

<style>
.text-center{text-align:center}
.mb-12{margin-bottom:48px}
.mb-3{margin-bottom:12px}
.mb-1{margin-bottom:4px}
.wizard-head{font-size:1.3rem;font-weight:800;color:var(--text);letter-spacing:-.01em}
.wizard-sub{font-size:.82rem;color:var(--secondary);margin-top:2px}
.panel-title{font-size:1.125rem;font-weight:700;color:var(--text)}
.panel-sub{font-size:.82rem;color:var(--secondary);margin-top:2px}
.result-title{font-size:1.25rem;font-weight:800;color:var(--text);letter-spacing:-.01em}
.tool-name{font-size:1rem;font-weight:700;color:var(--text)}
.tool-desc{font-size:.8rem;line-height:1.6;margin-bottom:12px;color:var(--secondary);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.rc-head-ic{width:36px;height:36px;border-radius:var(--radius);background:var(--accent-subtle);color:var(--accent);border:1px solid rgba(31,111,67,.22);flex-shrink:0;font-weight:700;font-size:.8rem;display:flex;align-items:center;justify-content:center}
</style>

<main class="w" style="padding-top:88px;padding-bottom:60px;">

  <div class="text-center mb-12 sf s1">
    <h1 class="wizard-head mb-3">Find Your Perfect AI Tool</h1>
    <p class="t-s" style="font-size:.875rem;max-width:560px;margin:0 auto;">
      Answer a few questions and we will match you with the best AI tools for your needs.
    </p>
  </div>

  <div style="max-width:720px;margin:0 auto;">
    <form id="recommendForm" method="POST" action="recommend.php">
      <div class="c">

        <div class="f g3" style="justify-content:space-between;margin-bottom:32px;">
          <div class="f" id="stepTrack">
            <div class="sd sd-a" id="dot-1"></div>
            <div class="sc" id="conn-1"></div>
            <div class="sd" id="dot-2"></div>
            <div class="sc" id="conn-2"></div>
            <div class="sd" id="dot-3"></div>
            <div class="sc" id="conn-3"></div>
            <div class="sd" id="dot-4"></div>
          </div>
          <span class="t-m" style="font-size:.78rem;font-weight:600;" id="stepLabel">Step 1 of 4</span>
        </div>

        <div class="step-panel" id="panel-1">
          <div class="f g3" style="margin-bottom:24px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
              <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
            </svg>
            <div>
              <h2 class="panel-title">What type of AI tool do you need?</h2>
              <p class="panel-sub">Choose the category that best matches your project</p>
            </div>
          </div>
          <div class="g gc2 g2" id="categoryGrid">
            <?php while ($cat = $categories->fetch_assoc()): ?>
            <label class="oc<?= $selectedCatId == $cat['id'] ? ' s' : '' ?>" data-value="<?= $cat['id'] ?>">
              <input type="radio" name="category_id" value="<?= $cat['id'] ?>"
                <?= $selectedCatId == $cat['id'] ? 'checked' : '' ?>>
              <?= htmlspecialchars($cat['category_name']) ?>
            </label>
            <?php endwhile; ?>
          </div>
        </div>

        <div class="step-panel h" id="panel-2">
          <div class="f g3" style="margin-bottom:24px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            <div>
              <h2 class="panel-title">Describe your use case</h2>
              <p class="panel-sub">Tell us more about what you are building or working on</p>
            </div>
          </div>
          <textarea name="use_case" id="useCaseInput" rows="4" class="i"
            placeholder="e.g. I need to generate marketing images for my blog, or I want to automate data analysis for my research project..."><?= htmlspecialchars($useCaseInput) ?></textarea>
          <div class="f" style="flex-wrap:wrap;margin-top:16px;">
            <?php foreach ($quickPrompts as $prompt): ?>
            <span class="qf" onclick="fillUseCase(this)"><?= htmlspecialchars($prompt) ?></span>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="step-panel h" id="panel-3">
          <div class="f g3" style="margin-bottom:24px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            <div>
              <h2 class="panel-title">What is your budget?</h2>
              <p class="panel-sub">Select your preferred pricing model</p>
            </div>
          </div>
          <div class="g gc3 g3" id="pricingGrid">
            <?php foreach ($pricingOptions as $key): $meta = $pricingMeta[$key]; ?>
            <label class="pc<?= $selectedPricing === $key ? ' s' : '' ?>" data-value="<?= $key ?>">
              <input type="radio" name="pricing" value="<?= $key ?>" <?= $selectedPricing === $key ? 'checked' : '' ?>>
              <div class="pci">
                <?php if ($key === 'Free'): ?>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                </svg>
                <?php elseif ($key === 'Freemium'): ?>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <?php else: ?>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
                <?php endif; ?>
              </div>
              <div class="pcn"><?= $key ?></div>
              <div class="pcd"><?= $meta['desc'] ?></div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="step-panel h" id="panel-4">
          <div class="f g3" style="margin-bottom:24px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
            <div>
              <h2 class="panel-title">Review your selections</h2>
              <p class="panel-sub">Check your answers before we find the best tools</p>
            </div>
          </div>
          <div class="f" style="flex-direction:column;gap:8px;" id="reviewSummary">
            <div class="rr">
              <span class="rl">Category</span>
              <span class="rv" id="reviewCategory">--</span>
            </div>
            <div class="rr">
              <span class="rl">Use Case</span>
              <span class="rv" id="reviewUseCase">--</span>
            </div>
            <div class="rr">
              <span class="rl">Budget</span>
              <span class="rv" id="reviewPricing">--</span>
            </div>
          </div>
        </div>

        <div class="f" style="justify-content:space-between;margin-top:32px;padding-top:24px;border-top:1px solid var(--border);">
          <button type="button" id="prevBtn" onclick="prevStep()" class="btn btn-secondary h">Back</button>
          <div class="f g2">
            <button type="button" id="nextBtn" onclick="nextStep()" class="btn btn-primary">Continue</button>
            <button type="submit" id="submitBtn" onclick="return validateStep()" class="btn btn-primary h">Find My Tools</button>
          </div>
        </div>
      </div>
    </form>

    <?php if ($isSearch): ?>
    <div style="margin-top:56px;" id="resultsSection">
      <div class="f g3" style="margin-bottom:24px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <div>
          <h2 class="result-title">Recommendations</h2>
          <p class="t-s" style="font-size:.82rem;margin-top:2px;">
            <?php if ($hasResults): ?>
            <?= $results->num_rows ?> tool<?= $results->num_rows > 1 ? 's' : '' ?> matched your criteria
            <?php else: ?>
            No tools matched your criteria
            <?php endif; ?>
          </p>
        </div>
      </div>

      <?php if ($hasResults): ?>
      <div class="g gc2 g4">
        <?php while ($tool = $results->fetch_assoc()):
          $matchScore = 0;
          $matchReasons = [];

          if ((int)$tool['category_id'] === $selectedCatId) {
            $matchScore += 50;
            $matchReasons[] = 'Category match';
          }
          if ($tool['pricing'] === $selectedPricing) {
            $matchScore += 30;
            $matchReasons[] = 'Pricing match';
          }
          if ($useCaseInput !== '') {
            $desc = strtolower(($tool['description'] ?? '') . ' ' . ($tool['tool_name'] ?? ''));
            $keywords = array_unique(array_filter(explode(' ', strtolower($useCaseInput)), fn($w) => strlen($w) > 3));
            $matched = 0;
            foreach ($keywords as $kw) {
              if (strpos($desc, $kw) !== false) $matched++;
            }
            if ($matched > 0) {
              $matchScore += min(20, $matched * 5);
              $matchReasons[] = 'Keyword relevance';
            }
          }

          $matchClass = $matchScore >= 70 ? 'mb-h' : ($matchScore >= 40 ? 'mb-m' : 'mb-l');
          $rating = (float)($tool['rating'] ?? 4.5);
          $fullStars = min(5, max(0, (int)round($rating)));
        ?>
        <div class="rc">
          <div class="f" style="justify-content:space-between;margin-bottom:12px;">
            <div class="f g3">
              <div class="rc-head-ic"><?= strtoupper(substr($tool['tool_name'], 0, 2)) ?></div>
              <div>
                <h3 class="tool-name"><?= htmlspecialchars($tool['tool_name']) ?></h3>
                <div class="f g2" style="margin-top:4px;">
                  <span class="tag tag-accent"><?= htmlspecialchars($tool['category_name'] ?? 'General') ?></span>
                  <span class="tag tag-gray"><?= htmlspecialchars($tool['pricing']) ?></span>
                </div>
              </div>
            </div>
            <span class="mb <?= $matchClass ?>" style="flex-shrink:0;"><?= $matchScore ?>%</span>
          </div>
          <p class="tool-desc"><?= htmlspecialchars(mb_substr($tool['description'] ?? '', 0, 200)) ?></p>
          <div class="f" style="justify-content:space-between;align-items:center;padding-top:12px;margin-top:auto;border-top:1px solid var(--border);">
            <div class="f g2">
              <?php for ($i = 0; $i < 5; $i++): ?>
              <svg class="star-svg" width="14" height="14" viewBox="0 0 24 24" fill="<?= $i < $fullStars ? 'var(--star)' : 'none' ?>" stroke="<?= $i < $fullStars ? 'var(--star)' : 'var(--border)' ?>" stroke-width="1.5">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
              </svg>
              <?php endfor; ?>
              <span class="t-m" style="font-size:.7rem;"><?= number_format($rating, 1) ?></span>
            </div>
            <a href="tool_detail.php?id=<?= $tool['id'] ?>" class="btn btn-secondary btn-sm">View Details</a>
          </div>
          <?php if ($matchReasons): ?>
          <div class="f g2" style="flex-wrap:wrap;margin-top:12px;">
            <?php foreach ($matchReasons as $reason): ?>
            <span class="mr"><?= $reason ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endwhile; ?>
      </div>
      <?php else: ?>
      <div class="c" style="text-align:center;padding:56px 24px;">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 16px;display:block;">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <h3 style="font-size:1.125rem;font-weight:800;color:var(--text);margin-bottom:4px;">No matching tools found</h3>
        <p class="t-s" style="font-size:.85rem;max-width:360px;margin:0 auto 20px;">
          Try broadening your search or adjusting your preferences to discover more AI tools.
        </p>
        <a href="tools.php" class="btn btn-primary">Browse All Tools</a>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</main>

<script>
var currentStep = 1;
var totalSteps = 4;

function showPanel(n) {
  for (var i = 1; i <= totalSteps; i++) {
    var p = document.getElementById('panel-' + i);
    if (i === n) p.classList.remove('h'); else p.classList.add('h');
  }
}

function updateProgress() {
  for (var i = 1; i <= totalSteps; i++) {
    var dot = document.getElementById('dot-' + i);
    dot.className = 'sd';
    if (i < currentStep) dot.classList.add('sd-d');
    else if (i === currentStep) dot.classList.add('sd-a');
  }
  for (var i = 1; i < totalSteps; i++) {
    var conn = document.getElementById('conn-' + i);
    conn.className = 'sc';
    if (i < currentStep) conn.classList.add('sc-d');
  }
  document.getElementById('stepLabel').textContent = 'Step ' + currentStep + ' of ' + totalSteps;

  var prevBtn = document.getElementById('prevBtn');
  var nextBtn = document.getElementById('nextBtn');
  var submitBtn = document.getElementById('submitBtn');

  if (currentStep === 1) prevBtn.classList.add('h'); else prevBtn.classList.remove('h');
  if (currentStep === totalSteps) { nextBtn.classList.add('h'); submitBtn.classList.remove('h'); }
  else { nextBtn.classList.remove('h'); submitBtn.classList.add('h'); }

  if (currentStep === totalSteps) updateReview();
}

function validateStep() {
  if (currentStep === 1) {
    if (!document.querySelector('input[name="category_id"]:checked')) {
      var g = document.getElementById('categoryGrid');
      g.style.border = '1px solid rgba(220,38,38,0.3)';
      g.style.borderRadius = '12px';
      g.style.padding = '4px';
      setTimeout(function() {
        g.style.border = '';
        g.style.borderRadius = '';
        g.style.padding = '';
      }, 800);
      return false;
    }
  }
  if (currentStep === 2) {
    var val = document.getElementById('useCaseInput').value.trim();
    if (!val) {
      var inp = document.getElementById('useCaseInput');
      inp.style.borderColor = 'rgba(220,38,38,0.3)';
      setTimeout(function() { inp.style.borderColor = ''; }, 800);
      return false;
    }
  }
  if (currentStep === 3) {
    if (!document.querySelector('input[name="pricing"]:checked')) {
      var g = document.getElementById('pricingGrid');
      g.style.border = '1px solid rgba(220,38,38,0.3)';
      g.style.borderRadius = '12px';
      g.style.padding = '4px';
      setTimeout(function() {
        g.style.border = '';
        g.style.borderRadius = '';
        g.style.padding = '';
      }, 800);
      return false;
    }
  }
  return true;
}

function nextStep() {
  if (!validateStep()) return;
  if (currentStep < totalSteps) { currentStep++; showPanel(currentStep); updateProgress(); }
}

function prevStep() {
  if (currentStep > 1) { currentStep--; showPanel(currentStep); updateProgress(); }
}

function updateReview() {
  var catRadio = document.querySelector('input[name="category_id"]:checked');
  if (catRadio) {
    var label = document.querySelector('.oc[data-value="' + catRadio.value + '"]');
    document.getElementById('reviewCategory').textContent = label ? label.textContent.trim() : 'Selected';
  }
  var useCase = document.getElementById('useCaseInput').value.trim();
  document.getElementById('reviewUseCase').textContent = useCase || 'Not specified';
  var pricingRadio = document.querySelector('input[name="pricing"]:checked');
  if (pricingRadio) {
    document.getElementById('reviewPricing').textContent = pricingRadio.value;
  }
}

function fillUseCase(el) {
  document.getElementById('useCaseInput').value = el.textContent.trim();
}

document.querySelectorAll('.oc').forEach(function(card) {
  card.addEventListener('click', function() {
    var group = this.closest('.g');
    group.querySelectorAll('.oc').forEach(function(c) { c.classList.remove('s'); });
    this.classList.add('s');
    this.querySelector('input').checked = true;
  });
});

document.querySelectorAll('.pc').forEach(function(card) {
  card.addEventListener('click', function() {
    var group = this.closest('.g');
    group.querySelectorAll('.pc').forEach(function(c) { c.classList.remove('s'); });
    this.classList.add('s');
    this.querySelector('input').checked = true;
  });
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Enter' && !e.target.matches('textarea, input')) {
    e.preventDefault();
    var nextBtn = document.getElementById('nextBtn');
    if (!nextBtn.classList.contains('h')) { nextStep(); return; }
    var submitBtn = document.getElementById('submitBtn');
    if (!submitBtn.classList.contains('h')) { document.getElementById('recommendForm').submit(); }
  }
  if (e.key === 'Escape' && currentStep > 1) { prevStep(); }
});

showPanel(currentStep);
updateProgress();
</script>

<?php include 'footer.php'; ?>
