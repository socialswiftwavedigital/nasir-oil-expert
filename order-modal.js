/* COD / WA Order Modal — nasiroilexpert.com */
(function () {
  var modalHtml = '<div id="codModal" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;">' +
    '<div onclick="closeCodModal()" style="position:absolute;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);"></div>' +
    '<div id="codModalInner" style="position:relative;background:#fff;border-radius:18px;padding:28px;width:min(460px,94vw);max-height:92vh;overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.28);">' +
      '<button onclick="closeCodModal()" style="position:absolute;top:12px;right:14px;width:30px;height:30px;border-radius:50%;border:none;background:#f2f2f2;font-size:1.1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">&times;</button>' +
      '<h3 id="codModalTitle" style="font-family:Montserrat,sans-serif;font-size:1rem;font-weight:700;color:#1B4332;margin:0 0 4px;padding-right:30px;"></h3>' +
      '<p id="codModalSub" style="font-size:.75rem;color:#888;margin:0 0 14px;"></p>' +
      '<div id="codProductBadge" style="background:#f0f7f4;border:1px solid #c8e8d4;border-radius:8px;padding:10px 14px;font-family:Montserrat,sans-serif;font-size:.82rem;color:#1B4332;margin-bottom:18px;"></div>' +
      '<form id="codModalForm" onsubmit="return false;">' +
        '<input type="hidden" id="codProdName">' +
        '<input type="hidden" id="codProdPrice">' +
        '<div id="codFieldsWrap"></div>' +
        '<div id="codPriceSummary" style="background:#f0f7f4;border:1px solid #c8e8d4;border-radius:8px;padding:10px 14px;margin:10px 0;font-family:Montserrat,sans-serif;font-size:.82rem;"></div>' +
        '<button type="button" id="codSubmitBtn" onclick="codSubmit()" style="width:100%;padding:14px;color:#fff;border:none;border-radius:30px;font-family:Montserrat,sans-serif;font-size:.84rem;font-weight:700;cursor:pointer;margin-top:6px;transition:.2s;display:flex;align-items:center;justify-content:center;gap:8px;">' +
          '<span id="codSubmitIcon"></span>' +
          '<span id="codSubmitText"></span>' +
        '</button>' +
      '</form>' +
      '<div id="codSuccessDiv" style="display:none;text-align:center;padding:24px 0;">' +
        '<div style="font-size:3rem;color:#52b788;margin-bottom:10px;">&#10003;</div>' +
        '<h4 id="codSuccessTitle" style="font-family:Montserrat,sans-serif;color:#1B4332;margin-bottom:6px;font-size:1rem;"></h4>' +
        '<p id="codSuccessSub" style="font-size:.82rem;color:#555;"></p>' +
      '</div>' +
    '</div>' +
  '</div>';

  document.addEventListener('DOMContentLoaded', function () {
    document.body.insertAdjacentHTML('beforeend', modalHtml);
  });
})();

var _waOnly = false;

var _waSvg = '<svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';

function _isUr() {
  return document.documentElement.classList.contains('lang-ur');
}

function _omField(id, type, labelEn, labelUr, phEn, phUr, required) {
  var ur = _isUr();
  var lbl = ur ? labelUr : labelEn;
  var ph = ur ? phUr : phEn;
  var req = required ? ' required' : '';
  var urStyle = ur ? 'font-family:Noto Nastaliq Urdu,serif;direction:rtl;text-align:right;text-transform:none;letter-spacing:0;' : '';
  var tag = type === 'textarea' ? 'textarea' : 'input';
  var inner = type === 'textarea'
    ? '<textarea id="' + id + '" placeholder="' + ph + '" rows="3"' + req +
        ' style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-family:Montserrat,sans-serif;font-size:.84rem;outline:none;box-sizing:border-box;resize:vertical;transition:.2s;' + urStyle + '"' +
        ' onfocus="this.style.borderColor=\'#2D6A4F\'" onblur="this.style.borderColor=\'#e2e8f0\'"></textarea>'
    : '<input type="' + type + '" id="' + id + '" placeholder="' + ph + '"' + req +
        ' style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-family:Montserrat,sans-serif;font-size:.84rem;outline:none;box-sizing:border-box;transition:.2s;' + urStyle + '"' +
        ' onfocus="this.style.borderColor=\'#2D6A4F\'" onblur="this.style.borderColor=\'#e2e8f0\'">';
  var urLblStyle = ur ? 'font-family:Noto Nastaliq Urdu,serif;direction:rtl;display:block;text-transform:none;letter-spacing:0;font-size:.8rem;' : '';
  return '<div style="margin-bottom:12px;">' +
    '<label for="' + id + '" style="display:block;font-family:Montserrat,sans-serif;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#333;margin-bottom:5px;' + urLblStyle + '">' + lbl + '</label>' +
    inner + '</div>';
}

function _openModal(product, price, waMode) {
  var modal = document.getElementById('codModal');
  if (!modal) return;

  _waOnly = waMode;
  var ur = _isUr();
  var delivery = 250;
  var total = Number(price) + delivery;

  if (waMode) {
    document.getElementById('codModalTitle').textContent = ur ? 'واٹس ایپ پر آرڈر دیں' : 'Order via WhatsApp';
    document.getElementById('codModalSub').textContent = ur ? 'تفصیل بھریں — آرڈر واٹس ایپ پر جائے گا' : 'Fill your details — order will open on WhatsApp';
  } else {
    document.getElementById('codModalTitle').textContent = ur ? 'آرڈر دیں — کیش آن ڈیلیوری' : 'Cash on Delivery Order';
    document.getElementById('codModalSub').textContent = ur ? 'اپنی تفصیل بھریں — ہم جلد رابطہ کریں گے' : 'Fill your details — we will contact you shortly';
  }

  document.getElementById('codProdName').value = product;
  document.getElementById('codProdPrice').value = price;
  document.getElementById('codProductBadge').innerHTML =
    '<strong>' + product + '</strong> &nbsp;&mdash;&nbsp; <strong style="color:#B8860B;">Rs ' + Number(price).toLocaleString() + '</strong>';

  document.getElementById('codFieldsWrap').innerHTML =
    _omField('codName',  'text',     'Full Name',         'پورا نام',         'Ahmad Ali',                 'آپ کا نام',               true) +
    _omField('codPhone', 'tel',      'WhatsApp / Phone',  'واٹس ایپ / فون',   '03XX-XXXXXXX',              '03XX-XXXXXXX',            true) +
    _omField('codCity',  'text',     'City',              'شہر',              'Karachi / Lahore...',        'کراچی / لاہور...',         true) +
    _omField('codAddr',  'textarea', 'Delivery Address',  'گھر کا پتہ',       'House No, Street, Area...', 'گھر نمبر، گلی، علاقہ...', true) +
    _omField('codNote',  'textarea', 'Notes (optional)',  'نوٹ (اختیاری)',    'Any special instructions...','کوئی خاص ہدایات...',      false);

  document.getElementById('codPriceSummary').innerHTML =
    '<div style="display:flex;justify-content:space-between;margin-bottom:4px;color:#555;">' +
      (ur ? 'ڈیلیوری چارج: <strong style="color:#333;">Rs ' + delivery + '</strong>' : 'Delivery Charges: <strong style="color:#333;">Rs ' + delivery + '</strong>') +
    '</div>' +
    '<div style="display:flex;justify-content:space-between;font-weight:700;color:#1B4332;">' +
      (ur ? 'کل رقم: <strong style="color:#B8860B;">Rs ' + total.toLocaleString() + '</strong>' : 'Total Payable: <strong style="color:#B8860B;">Rs ' + total.toLocaleString() + '</strong>') +
    '</div>';

  var btn = document.getElementById('codSubmitBtn');
  var icon = document.getElementById('codSubmitIcon');
  if (waMode) {
    btn.style.background = 'linear-gradient(135deg,#25D366,#1aab55)';
    icon.innerHTML = _waSvg;
    document.getElementById('codSubmitText').textContent = ur ? 'واٹس ایپ پر آرڈر بھیجیں' : 'Send Order on WhatsApp';
  } else {
    btn.style.background = 'linear-gradient(135deg,#B8860B,#8B6914)';
    icon.innerHTML = '&#128204;';
    document.getElementById('codSubmitText').textContent = ur ? 'آرڈر دیں — کیش آن ڈیلیوری' : 'Place COD Order';
  }

  document.getElementById('codSuccessDiv').style.display = 'none';
  document.getElementById('codModalForm').style.display = 'block';

  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function openCodModal(product, price) { _openModal(product, price, false); }
function openWaModal(product, price)  { _openModal(product, price, true);  }

function closeCodModal() {
  var modal = document.getElementById('codModal');
  if (modal) modal.style.display = 'none';
  document.body.style.overflow = '';
}

function codSubmit() {
  var name  = (document.getElementById('codName').value || '').trim();
  var phone = (document.getElementById('codPhone').value || '').trim();
  var city  = (document.getElementById('codCity').value || '').trim();
  var addr  = (document.getElementById('codAddr').value || '').trim();
  var note  = (document.getElementById('codNote').value || '').trim();
  var prod  = document.getElementById('codProdName').value;
  var price = Number(document.getElementById('codProdPrice').value);
  var delivery = 250;
  var total = price + delivery;

  var ur = _isUr();
  if (!name || !phone) {
    alert(ur ? 'نام اور فون نمبر ضرور بھریں۔' : 'Please enter your name and phone number.');
    return;
  }

  var btn = document.getElementById('codSubmitBtn');
  btn.disabled = true;
  btn.style.opacity = '.65';
  document.getElementById('codSubmitText').textContent = ur ? 'بھیج رہے ہیں...' : 'Sending...';

  var lines = [
    '🛒 *' + (ur ? 'نیا آرڈر' : 'New Order') + '*',
    '',
    '📦 *' + prod + '* — Rs ' + price.toLocaleString(),
    '🚚 ' + (ur ? 'ڈیلیوری:' : 'Delivery:') + ' Rs ' + delivery,
    '💰 *' + (ur ? 'کل رقم:' : 'Total:') + ' Rs ' + total.toLocaleString() + '* (COD)',
    '',
    '👤 *' + (ur ? 'نام:' : 'Name:') + '* ' + name,
    '📱 *' + (ur ? 'فون:' : 'Phone:') + '* ' + phone
  ];
  if (city) lines.push('🏙️ *' + (ur ? 'شہر:' : 'City:') + '* ' + city);
  if (addr) lines.push('📍 *' + (ur ? 'پتہ:' : 'Address:') + '* ' + addr);
  if (note) lines.push('💬 *' + (ur ? 'نوٹ:' : 'Notes:') + '* ' + note);
  lines.push('', '🌍 nasiroilexpert.com');

  function _showSuccess(titleEn, titleUr, subEn, subUr) {
    btn.disabled = false;
    btn.style.opacity = '1';
    document.getElementById('codModalForm').style.display = 'none';
    document.getElementById('codSuccessDiv').style.display = 'block';
    document.getElementById('codSuccessTitle').textContent = ur ? titleUr : titleEn;
    document.getElementById('codSuccessSub').textContent = ur ? subUr : subEn;
  }

  if (_waOnly) {
    var waUrl = 'https://wa.me/923211112280?text=' + encodeURIComponent(lines.join('\n'));
    window.open(waUrl, '_blank');
    _showSuccess(
      'WhatsApp Opened!', 'واٹس ایپ کھل گیا!',
      'Your order details were sent on WhatsApp.', 'آپ کا آرڈر واٹس ایپ پر بھیجا گیا۔'
    );
  } else {
    var emailBody = prod + ' x1 = Rs ' + price.toLocaleString() + '\nDelivery: Rs ' + delivery + '\nTotal: Rs ' + total.toLocaleString() + ' (COD)';
    fetch('/send-order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: name, phone: phone, purpose: 'order',
        city: city, address: addr, note: note,
        body: emailBody
      })
    })
    .catch(function () {})
    .finally(function () {
      _showSuccess(
        'Order Received!', 'آرڈر موصول ہوا!',
        'Your COD order was received. We will contact you shortly.', 'آپ کا COD آرڈر موصول ہوا۔ ہم جلد رابطہ کریں گے۔'
      );
    });
  }
}
