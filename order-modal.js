/* COD Order Modal — nasiroilexpert.com */
(function () {
  var html = [
    '<div id="codModal" style="display:none;position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;">',
      '<div onclick="closeCodModal()" style="position:absolute;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);"></div>',
      '<div style="position:relative;background:#fff;border-radius:18px;padding:32px;width:min(460px,94vw);max-height:92vh;overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.28);">',
        '<button onclick="closeCodModal()" style="position:absolute;top:14px;right:14px;width:30px;height:30px;border-radius:50%;border:none;background:#f2f2f2;font-size:1.1rem;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center;">&times;</button>',
        '<h3 style="font-family:Montserrat,sans-serif;font-size:1rem;font-weight:700;color:#1B4332;margin-bottom:6px;">Cash on Delivery Order</h3>',
        '<p style="font-size:.75rem;color:#888;margin-bottom:16px;">Apni details fill karein — hum apsy rabta karein gay</p>',
        '<div id="codProductBadge" style="background:#f0f7f4;border:1px solid #c8e8d4;border-radius:8px;padding:10px 14px;font-family:Montserrat,sans-serif;font-size:.82rem;color:#1B4332;margin-bottom:20px;"></div>',
        '<form id="codModalForm">',
          '<input type="hidden" id="codProdName">',
          '<input type="hidden" id="codProdPrice">',
          codField('codName',    'text',     'Full Name *',         'Apna poora naam'),
          codField('codPhone',   'tel',      'Phone Number *',      '03XX-XXXXXXX'),
          codField('codWA',      'tel',      'WhatsApp Number *',   '03XX-XXXXXXX'),
          codField('codCity',    'text',     'City *',              'Lahore / Karachi / Islamabad...'),
          codField('codNearby',  'text',     'Nearby Place / Landmark', 'Masjid, School, Market...'),
          codFieldTA('codAddr',              'Full Address *',      'Ghar ka pura pata likhein...'),
          '<div id="codPriceSummary" style="background:#f0f7f4;border:1px solid #c8e8d4;border-radius:8px;padding:12px 16px;margin:12px 0;font-family:Montserrat,sans-serif;font-size:.82rem;"></div>',
          '<button type="submit" id="codSubmitBtn" style="width:100%;padding:14px;background:#1A1A1A;color:#fff;border:none;border-radius:30px;font-family:Montserrat,sans-serif;font-size:.82rem;font-weight:700;letter-spacing:.06em;cursor:pointer;margin-top:4px;transition:.2s;">',
            '&#128444; Place Order — Cash on Delivery',
          '</button>',
        '</form>',
        '<div id="codSuccessDiv" style="display:none;text-align:center;padding:24px 0;">',
          '<div style="font-size:3rem;color:#52b788;margin-bottom:12px;">&#10003;</div>',
          '<h4 style="font-family:Montserrat,sans-serif;color:#1B4332;margin-bottom:8px;font-size:1rem;">Order Placed!</h4>',
          '<p style="font-size:.82rem;color:#555;margin-bottom:6px;">Hum aapsy jald rabta karein gay. Shukriya!</p>',
          '<p id="codOrdIdLine" style="font-size:.72rem;color:#aaa;"></p>',
        '</div>',
      '</div>',
    '</div>'
  ].join('');

  function codField(id, type, label, ph) {
    return '<div style="margin-bottom:13px;">' +
      '<label for="' + id + '" style="display:block;font-family:Montserrat,sans-serif;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#333;margin-bottom:5px;">' + label + '</label>' +
      '<input type="' + type + '" id="' + id + '" placeholder="' + ph + '" required ' +
        'style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-family:Montserrat,sans-serif;font-size:.84rem;outline:none;box-sizing:border-box;transition:.2s;" ' +
        'onfocus="this.style.borderColor=\'#2D6A4F\'" onblur="this.style.borderColor=\'#e2e8f0\'">' +
      '</div>';
  }

  function codFieldTA(id, label, ph) {
    return '<div style="margin-bottom:13px;">' +
      '<label for="' + id + '" style="display:block;font-family:Montserrat,sans-serif;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#333;margin-bottom:5px;">' + label + '</label>' +
      '<textarea id="' + id + '" placeholder="' + ph + '" required rows="3" ' +
        'style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-family:Montserrat,sans-serif;font-size:.84rem;outline:none;box-sizing:border-box;resize:vertical;transition:.2s;" ' +
        'onfocus="this.style.borderColor=\'#2D6A4F\'" onblur="this.style.borderColor=\'#e2e8f0\'"></textarea>' +
      '</div>';
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.body.insertAdjacentHTML('beforeend', html);
    document.getElementById('codModalForm').addEventListener('submit', _submitCod);
  });

  function _submitCod(e) {
    e.preventDefault();
    var btn = document.getElementById('codSubmitBtn');
    btn.textContent = 'Placing Order...';
    btn.disabled = true;
    btn.style.opacity = '.65';

    var fd = new FormData();
    var prodPrice = Number(document.getElementById('codProdPrice').value);
    var delivery  = 250;
    fd.append('product',  document.getElementById('codProdName').value);
    fd.append('price',    prodPrice);
    fd.append('delivery', delivery);
    fd.append('total',    prodPrice + delivery);
    fd.append('name',     document.getElementById('codName').value.trim());
    fd.append('phone',    document.getElementById('codPhone').value.trim());
    fd.append('whatsapp', document.getElementById('codWA').value.trim());
    fd.append('city',     document.getElementById('codCity').value.trim());
    fd.append('nearby',   document.getElementById('codNearby').value.trim());
    fd.append('address',  document.getElementById('codAddr').value.trim());
    fd.append('source',   window.location.href);

    fetch('order-handler.php', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.success) {
          document.getElementById('codModalForm').style.display = 'none';
          document.getElementById('codSuccessDiv').style.display = 'block';
          document.getElementById('codOrdIdLine').textContent = 'Order ID: ' + res.order_id;
        } else {
          _resetBtn(btn);
          alert('Error: ' + (res.error || 'Please try again.'));
        }
      })
      .catch(function () {
        _resetBtn(btn);
        alert('Connection error. Please contact us on WhatsApp!');
      });
  }

  function _resetBtn(btn) {
    btn.textContent = '📌 Place Order — Cash on Delivery';
    btn.disabled = false;
    btn.style.opacity = '1';
  }
})();

function openCodModal(product, price) {
  var modal = document.getElementById('codModal');
  if (!modal) return;
  document.getElementById('codProdName').value = product;
  document.getElementById('codProdPrice').value = price;
  document.getElementById('codProductBadge').innerHTML =
    '<strong>' + product + '</strong> &nbsp;&mdash;&nbsp; <strong style="color:#B8860B;">Rs ' +
    Number(price).toLocaleString() + '</strong> &nbsp;|&nbsp; Cash on Delivery';
  document.getElementById('codSuccessDiv').style.display = 'none';
  document.getElementById('codModalForm').style.display = 'block';
  var btn = document.getElementById('codSubmitBtn');
  btn.textContent = '📌 Place Order — Cash on Delivery';
  btn.disabled = false; btn.style.opacity = '1';
  document.getElementById('codName').value = '';
  document.getElementById('codPhone').value = '';
  document.getElementById('codWA').value = '';
  document.getElementById('codCity').value = '';
  document.getElementById('codNearby').value = '';
  document.getElementById('codAddr').value = '';
  var delivery = 250;
  var total = Number(price) + delivery;
  document.getElementById('codPriceSummary').innerHTML =
    '<div style="display:flex;justify-content:space-between;margin-bottom:5px;color:#555;">Delivery Charges: <strong style="color:#333;">Rs ' + delivery + '</strong></div>' +
    '<div style="display:flex;justify-content:space-between;font-weight:700;font-size:.9rem;color:#1B4332;">Total Payable: <strong style="color:#B8860B;">Rs ' + total.toLocaleString() + '</strong></div>';
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeCodModal() {
  var modal = document.getElementById('codModal');
  if (modal) modal.style.display = 'none';
  document.body.style.overflow = '';
}
