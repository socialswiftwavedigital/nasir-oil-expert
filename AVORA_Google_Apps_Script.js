// ============================================================
//  AVORA — Google Apps Script  (paste this into script.google.com)
//  Run -> Deploy -> New deployment -> Web App
//  Execute as: Me | Who can access: Anyone
// ============================================================

const SPREADSHEET_ID = ""; // ← paste your Google Sheet ID here

function doPost(e) {
  try {
    var data = JSON.parse(e.postData.contents);
    var ss = SpreadsheetApp.openById(SPREADSHEET_ID);

    // ── Orders sheet ─────────────────────────────────────────
    var ordersSheet = ss.getSheetByName("Orders");
    if (!ordersSheet) {
      ordersSheet = ss.insertSheet("Orders");
      ordersSheet.appendRow([
        "Order Ref", "Date", "Time", "Customer Name", "Phone", "Email",
        "Address", "Notes", "Items (detail)", "SKUs",
        "Subtotal (Rs)", "Delivery (Rs)", "Total (Rs)", "Payment", "Status"
      ]);
      ordersSheet.getRange("1:1").setFontWeight("bold").setBackground("#2c2c2c").setFontColor("#d4af37");
      ordersSheet.setFrozenRows(1);
    }

    var dt   = new Date(data.timestamp);
    var date = Utilities.formatDate(dt, Session.getScriptTimeZone(), "yyyy-MM-dd");
    var time = Utilities.formatDate(dt, Session.getScriptTimeZone(), "HH:mm:ss");

    var itemDetail = (data.items || []).map(function(i) {
      return i.name + " x" + i.qty + " @ Rs." + i.unitPrice + " = Rs." + i.lineTotal;
    }).join(" | ");

    var skuList = (data.items || []).map(function(i) {
      return i.sku || "";
    }).filter(Boolean).join(", ");

    ordersSheet.appendRow([
      data.orderRef || "",
      date,
      time,
      data.customer ? data.customer.name  : "",
      data.customer ? data.customer.phone : "",
      data.customer ? data.customer.email : "",
      data.customer ? data.customer.address : "",
      data.customer ? data.customer.notes  : "",
      itemDetail,
      skuList,
      data.subtotal       || 0,
      data.deliveryCharges || 0,
      data.total          || 0,
      data.paymentMethod  || "COD",
      data.status         || "New"
    ]);

    // ── Auto-decrement inventory ──────────────────────────────
    var invSheet = ss.getSheetByName("Inventory");
    if (invSheet && data.items) {
      var invData = invSheet.getDataRange().getValues();
      data.items.forEach(function(item) {
        for (var r = 1; r < invData.length; r++) {
          if (invData[r][0] === item.sku) {
            var currentQty = Number(invData[r][2]) || 0;
            invSheet.getRange(r + 1, 3).setValue(Math.max(0, currentQty - item.qty));
            break;
          }
        }
      });
    }

    return ContentService
      .createTextOutput(JSON.stringify({ status: "ok" }))
      .setMimeType(ContentService.MimeType.JSON);

  } catch (err) {
    return ContentService
      .createTextOutput(JSON.stringify({ status: "error", message: err.toString() }))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

// ── Run this function once to set up the Inventory sheet ─────
function setupInventory() {
  var ss = SpreadsheetApp.openById(SPREADSHEET_ID);
  var sheet = ss.getSheetByName("Inventory");
  if (!sheet) sheet = ss.insertSheet("Inventory");

  sheet.clearContents();
  sheet.appendRow(["SKU", "Product Name", "Stock Qty", "Low-Stock Alert"]);
  sheet.getRange("1:1").setFontWeight("bold").setBackground("#2c2c2c").setFontColor("#d4af37");
  sheet.setFrozenRows(1);

  var products = [
    ["AVR-NILA-001", "Moroccan Nila Soap",        50, 10],
    ["AVR-TURM-002", "Turmeric Kojic Soap",        50, 10],
    ["AVR-PINK-003", "Pink Clay Soap",              50, 10],
    ["AVR-CHAR-004", "Noir Rose Charcoal Soap",     50, 10],
    ["AVR-NEEM-005", "Neem Purifying Soap",         50, 10],
    ["AVR-SALT-006", "Himalayan Salt Soap",         50, 10],
    ["AVR-MULT-007", "Multani Mitti Soap",          50, 10],
    ["AVR-HRT-008",  "Heart Bliss Soap",            50, 10],
    ["AVR-OCN-009",  "Ocean Bloom Soap",            50, 10],
    ["AVR-LAV-010",  "Lavender Rose Soap",          50, 10],
    ["AVR-CRM-011",  "Crimson Rose Soap",           50, 10],
    ["AVR-SIDR-012", "Sidr & Argan Oil Soap",        30, 5],
    ["AVR-SEA-013",  "Seabuckthorn & Manuka Honey Soap", 20, 5],
  ];

  products.forEach(function(row) { sheet.appendRow(row); });

  // Conditional formatting: highlight row red when qty <= alert threshold
  var range = sheet.getRange("C2:C12");
  var rule = SpreadsheetApp.newConditionalFormatRule()
    .whenNumberLessThanOrEqualTo(10)
    .setBackground("#f4c7c3")
    .setRanges([range])
    .build();
  sheet.setConditionalFormatRules([rule]);

  SpreadsheetApp.getUi().alert("Inventory sheet created with all 11 AVORA products!");
}
