/* ============================================================
   Nasir Oil Expert — EN / UR Translation Engine
   ============================================================ */
(function () {
  var LANG_KEY = 'noe_lang';

  var dict = {
    en: {
      /* Navigation */
      'nav.home': 'Home',
      'nav.collections': 'Collections',
      'nav.about': 'About',
      'nav.reviews': 'Reviews',
      'nav.contact': 'Contact',
      'nav.bundleKit': 'Bundle Kit',
      'nav.goldenOil': 'Golden Oil',
      'nav.blackRoseOil': 'Black Rose Oil',
      'nav.herbalShampoo': 'Herbal Shampoo',
      'nav.orderNow': 'Order Now',
      'nav.cart': '🛒 Cart',

      /* Hero */
      'hero.since': 'Since 1948 · Nasir Oil Expert',
      'hero.h1': 'Pure &amp; Authentic<br><span class="highlight">Hair Care</span>',
      'hero.sub': 'Natural oils and herbal care crafted from the finest botanicals for stronger, shinier, healthier hair.',
      'hero.chip1': '100% Natural',
      'hero.chip2': 'Est. 1948',
      'hero.chip3': '5000+ Customers',
      'hero.chip4': 'Nationwide Delivery',

      /* Shop / Products section */
      'shop.label': 'Our Collections',
      'shop.title': 'Shop By Collection',
      'shop.sub': 'All products crafted with 100% natural ingredients for maximum hair health.',
      'filter.all': 'All Products',
      'filter.oil': 'Golden Oil',
      'filter.rose': 'Black Rose Oil',
      'filter.shampoo': 'Herbal Shampoo',

      /* Product tags */
      'tag.bestValue': 'Best Value',
      'tag.bestSeller': 'Best Seller',
      'tag.premium': 'Premium',
      'tag.natural': 'Natural',

      /* Product names */
      'prod.bundle': '3-in-1 Complete Kit',
      'prod.golden': 'Golden Oil',
      'prod.rose': 'Black Rose Oil',
      'prod.shampoo': 'Herbal Shampoo',

      /* Benefit chips */
      'ben.golden': '✦ Golden Oil',
      'ben.rose': '✦ Black Rose Oil',
      'ben.shampoo': '✦ Herbal Shampoo',
      'ben.growth': '✦ Hair Growth',
      'ben.nourish': '✦ Deep Nourish',
      'ben.shine': '✦ Shine Boost',
      'ben.strong': '✦ Strengthening',
      'ben.nobreak': '✦ Anti-Breakage',
      'ben.fragrant': '✦ Fragrant',
      'ben.cleanse': '✦ Gentle Cleanse',
      'ben.scalp': '✦ Scalp Care',
      'ben.sulfate': '✦ Sulfate Free',

      /* Descriptions */
      'desc.bundle': 'Complete 45-day hair treatment — fights hair fall, greying and dandruff. All 3 products in one special kit. Save Rs 750 on the full combo.',
      'desc.golden': 'Our signature Golden Oil is a rich blend of premium cold-pressed oils and herbal extracts. Deeply penetrates the hair shaft to moisturize, nourish, and stimulate healthy hair growth.',
      'desc.rose': 'Infused with rare black rose extract and luxurious botanical oils. Strengthens hair from root to tip with long-lasting fragrance and antioxidant protection.',
      'desc.shampoo': 'Formulated with neem, amla, bhringraj and botanical extracts. Gently cleanses without stripping natural oils. Soft, manageable, beautifully clean hair every wash.',

      /* Buttons */
      'btn.cart': '🛒 Add to Cart',
      'btn.details': 'View Details',
      'btn.wa': '📲 Order on WhatsApp',
      'btn.orderNow': 'Order Now',

      /* Bundle section */
      'bund.label': 'Special Deal',
      'bund.title': 'Complete Hair Treatment Kit',
      'bund.badge': '3-in-1 Bundle',
      'bund.items': 'Golden Oil + Black Rose Oil + Herbal Shampoo',
      'bund.save': 'Save Rs 750',
      'bund.oldPrice': 'Rs 4,000 separately',

      /* Why choose us */
      'why.label': 'Why Choose Us',
      'why.title': 'The Nasir Difference',
      'why.sub': 'Six decades of expertise distilled into every bottle.',
      'why.f1': '100% Natural',
      'why.f1d': 'Zero harmful chemicals, artificial fragrances or preservatives',
      'why.f2': 'Cold-Pressed',
      'why.f2d': 'Our signature extraction method preserves every active nutrient',
      'why.f3': 'Authentic Recipe',
      'why.f3d': 'Original 1948 formula, trusted by generations',
      'why.f4': 'Delivery',
      'why.f4d': 'Fast, nationwide shipping to your doorstep',

      /* Reels & Testimonials */
      'reels.label': 'Customer Reels',
      'reels.title': 'See Real Transformations',
      'test.label': 'Testimonials',
      'test.title': 'Real People. Real Results.',

      /* Products page */
      'pg.prod.label': 'Our Collections',
      'pg.prod.title': 'All Products',
      'pg.prod.sub': 'Shop our complete range of natural hair care products',

      /* Product detail — shared */
      'pd.badge.best': 'Best Seller',
      'pd.badge.natural': '100% Natural',
      'pd.badge.premium': 'Premium',
      'pd.badge.herbal': 'Herbal',
      'pd.tab.desc': 'Description',
      'pd.tab.benefits': 'Benefits',
      'pd.tab.ing': 'Ingredients',
      'pd.tab.use': 'How to Use',
      'pd.tab.rev': 'Reviews',
      'pd.delivery': '🚚 Free Delivery Nationwide',
      'pd.instock': '✓ In Stock',
      'pd.natural': '100% Natural & Certified',
      'pd.addCart': '🛒 Add to Cart',
      'pd.wa': '📲 Order on WhatsApp',

      /* Product detail — Golden Oil */
      'pd.golden.name': 'Golden Hair Oil',
      'pd.golden.sub': 'Premium Cold-Pressed Hair Growth Oil',

      /* Product detail — Black Rose Oil */
      'pd.rose.name': 'Black Rose Oil',
      'pd.rose.sub': 'Luxury Botanical Hair Strengthening Oil',

      /* Product detail — Herbal Shampoo */
      'pd.shampoo.name': 'Herbal Shampoo',
      'pd.shampoo.sub': 'Natural Herbal Hair Cleansing Shampoo',

      /* Product detail — Bundle Kit */
      'pd.bundle.name': '3-in-1 Complete Hair Kit',
      'pd.bundle.sub': 'Golden Oil + Black Rose Oil + Herbal Shampoo',

      /* About page */
      'ab.mission.label': 'Our Mission',
      'ab.values.label': 'Our Values',
      'ab.values.title': 'What We Stand For',
      'ab.values.sub': 'These core values guide everything we do — from how we source our ingredients to how we serve our customers.',
      'ab.journey.label': 'Our Journey',
      'ab.journey.title': 'How It All Started',
      'ab.journey.sub': 'From a small dream to thousands of happy customers — here\'s our story.',

      /* Contact page */
      'ct.label': 'Contact Us',
      'ct.title': 'Get in Touch',
      'ct.sub': 'We\'d love to hear from you. Place your order or ask any question.',

      /* Reviews page */
      'rv.label': 'Customer Reviews',
      'rv.title': 'What Our Customers Say',

      /* Bundle deal content */
      'bund.badge2': '3-in-1 Bundle',
      'bund.kit.title': 'Nasir Herbal Hair Complete Kit',
      'bund.kit.desc': '45-day complete treatment for hair fall, greying, dandruff and hair growth. All three products needed for the full 3-phase treatment, in one kit.',
      'bund.chip1': 'Golden Oil',
      'bund.chip2': 'Herbal Shampoo',
      'bund.chip3': 'Black Rose Oil',
      'bund.btn': '🛒 Add to Cart',
      'bund.viewDetails': 'View Details →',

      /* Why Choose Us cards */
      'why.c1.title': '100% Natural',
      'why.c1.desc': 'No synthetic chemicals. Pure botanical goodness in every drop.',
      'why.c2.title': 'Since 1948',
      'why.c2.desc': 'Over 78 years of expertise in authentic herbal hair care formulations.',
      'why.c3.title': 'Fast Delivery',
      'why.c3.desc': 'Quick and reliable delivery across Pakistan to your doorstep.',
      'why.c4.title': '5000+ Customers',
      'why.c4.desc': 'Trusted by thousands of satisfied customers across Pakistan.',

      /* Customer reviews section */
      'rev.label': 'Customer Reviews',
      'rev.title': 'What Our Customers Say',
      'rev.sub': 'Real results from real people across Pakistan.',

      /* Footer */
      'ft.tagline': 'Pure & Authentic Oils',
      'ft.brand.desc': 'Pure, authentic hair oils crafted since 1948. Trusted by thousands across Pakistan.',
      'ft.quickLinks': 'Quick Links',
      'ft.products': 'Products',
      'ft.contact': 'Contact',
      'ft.home': 'Home',
      'ft.collections': 'Collections',
      'ft.reviews': 'Reviews',
      'ft.about': 'About Us',
      'ft.contactLink': 'Contact',
      'ft.waOrder': 'WhatsApp Order',
      'ft.rights': '© 2026 Nasir Oil Expert. All rights reserved.',
      'ft.followUs': 'Follow Us',

      /* Products page product card buttons (same as index but separate occurrences) */
      'pg.viewDetails': 'View Details',
      'pg.addCart': '🛒 Add to Cart',

      /* Contact page */
      'ct.name': 'Your Name',
      'ct.phone': 'Phone Number',
      'ct.message': 'Your Message',
      'ct.send': 'Send Message',
      'ct.wa.title': 'Order via WhatsApp',
      'ct.wa.sub': 'Quick and easy ordering through WhatsApp',
      'ct.wa.btn': 'Chat on WhatsApp',

      /* Product detail — WhatsApp button */
      'pd.wa.order': 'Order via WhatsApp',
      'pd.cod.buy': '💵 Buy with Cash on Delivery',

      /* Cart page */
      'cart.title': 'Your Cart',
      'cart.empty': 'Your cart is empty',
      'cart.total': 'Total',
      'cart.checkout': 'Proceed to Checkout',
      'cart.continue': 'Continue Shopping',
      'cart.section.label': 'Your Selection',
      'cart.items': 'Cart Items',
      'cart.cont': '← Continue Shopping',
      'cart.summary': 'Order Summary',
      'cart.subtotal': 'Subtotal',
      'cart.delivery.label': 'Delivery',
      'cart.freedel': 'Rs 250 delivery charges — Free on orders over Rs 5,000!',
      'cart.wa.btn': 'WhatsApp Order',
      'cart.payment': 'Payment Methods',
      'cart.cod': '💵 Cash on Delivery',
      'cart.more.label': 'More Products',
      'cart.more.title': 'You May Also Like',
      'cart.h1': 'Shopping Cart',
      'bc.cart': 'Cart',
      'cart.golden.desc': 'Intense nourishment for dry &amp; damaged hair.',
      'cart.rose.desc': 'Luxurious rose extract for shine &amp; strength.',
      'cart.shampoo.desc': 'Gentle daily cleanse with natural botanicals.',
      'cart.herbal.badge': 'Herbal',

      /* Checkout page */
      'co.hero.label': 'Secure Checkout',
      'co.h1': 'Place Your Order',
      'bc.checkout': 'Checkout',
      'co.step.confirm': 'Confirm',
      'co.delivery.title': 'Delivery Information',
      'co.name.label': 'Full Name *',
      'co.phone.label': 'Phone Number *',
      'co.city.label': 'City *',
      'co.city.select': 'Select your city',
      'co.address.label': 'Full Address *',
      'co.payment.title': 'Payment Method',
      'co.cod.name': 'Cash on Delivery (COD)',
      'co.cod.sub': 'Pay cash when your order is delivered',
      'co.cod.info': 'Cash on Delivery — Pay at home when your order arrives. No advance payment needed.',
      'co.freedel.p': '✅ Rs 250 delivery charges — Free on orders over Rs 5,000!',
      'co.place.order': '🛒 Place Order',
      'co.wa.confirm': 'You will receive a WhatsApp confirmation after placing your order',
      'co.success.title': 'Order Placed!',
      'co.success.p1': 'Thank you! Your order has been received. You will get a WhatsApp confirmation shortly.',
      'co.success.p2': 'WhatsApp is opening — your order details will be sent automatically.',
      'co.success.home': 'Go to Home',
      'co.success.browse': 'Browse More Products',
      'co.back.cart': '← Back to Cart',
      'co.secure.title': 'Safe &amp; Secure',
      'co.secure.list': '🔒 Your information is secure<br>🚚 Fast nationwide delivery<br>💵 COD available<br>🔄 Easy returns',

      /* Nav logo */
      'nav.logo.name': 'Nasir Oil Expert',

      /* Marquee items */
      'marq.1': 'Pure Extracted Oils',
      'marq.2': 'Organic & Herbal',
      'marq.3': 'Golden Oil Collection',
      'marq.4': 'Black Rose Oil',
      'marq.5': 'Herbal Shampoo',
      'marq.6': '100% Natural',
      'marq.7': 'Since 1948',
      'marq.8': 'Nationwide Delivery',
      'marq.9': 'Authentic Formula',

      /* Footer product links */
      'ft.prod.bundle': 'Bundle Kit',
      'ft.prod.golden': 'Golden Oil',
      'ft.prod.rose': 'Black Rose Oil',
      'ft.prod.shampoo': 'Herbal Shampoo',
      'ft.prod.combos': 'Combo Deals',

      /* Breadcrumbs */
      'bc.home': 'Home',
      'bc.products': 'Products',
      'bc.about': 'About',
      'bc.golden': 'Golden Hair Oil',
      'bc.rose': 'Black Rose Hair Oil',
      'bc.shampoo': 'Herbal Shampoo',
      'bc.bundle': '3-in-1 Complete Kit',

      /* Product perks */
      'pd.perk.delivery': 'Rs 250 delivery • Free on Rs 5,000+',
      'pd.perk.cod': 'Cash on Delivery',
      'pd.perk.natural100': '100% Natural',
      'pd.perk.rose.infused': 'Rose Infused',
      'pd.perk.botanicals': '100% Natural Botanicals',
      'pd.perk.sulfate': 'Sulfate & Paraben Free',
      'pd.perk.allhair': 'Safe for All Hair Types',

      /* Tab buttons */
      'pd.tab.inside': "What's Inside",
      'pd.tab.why': 'Why Bundle',

      /* Product review section */
      'pd.rev.label': 'Customer Reviews',
      'pd.rev.title': 'What People Are Saying',
      'pd.rev.verified': '✓ Verified Purchase',
      'pd.rev.128': 'Based on 128 reviews',
      'pd.rev.96': 'Based on 96 reviews',
      'pd.rev.87': 'Based on 87 reviews',
      'pd.rev.214': 'Based on 214 reviews',

      /* Related / included products */
      'pd.rel.label': 'You May Also Like',
      'pd.rel.title': 'Related Products',
      'pd.kit.label': 'Included in This Kit',
      'pd.kit.title': 'Shop Individual Products',
      'pd.bundle.addcart': 'Add to Cart — Rs 3,250',

      /* Index review cards */
      'idx.rev.c1.q': '"After just 45 days of using the complete kit, my hair fall reduced by 80%. The Black Rose Oil works overnight. I wake up to softer, thicker hair."',
      'idx.rev.c1.name': 'Ayesha Khan',
      'idx.rev.c1.city': 'Lahore',
      'idx.rev.c2.q': '"Nasir Golden Oil has been a game changer. My hair went from dry and brittle to shiny and strong in one month. 100% natural, you can feel the difference."',
      'idx.rev.c2.name': 'Muhammad Usman',
      'idx.rev.c2.city': 'Karachi',
      'idx.rev.c3.q': '"The Herbal Shampoo is so gentle — no dryness, no frizz. My dandruff is completely gone after 3 weeks. Highly recommended!"',
      'idx.rev.c3.name': 'Sara Mahmood',
      'idx.rev.c3.city': 'Islamabad',
      'idx.rev.c4.q': '"I was skeptical at first but the results after 45 days speak for themselves. My scalp is healthier and hair loss has stopped completely."',
      'idx.rev.c4.name': 'Fatima Malik',
      'idx.rev.c4.city': 'Faisalabad',
      'idx.rev.c5.q': '"Black Rose Oil is my new favourite. The colour of my hair has improved and it looks so much thicker. Order from Nasir Oil Expert — pure quality!"',
      'idx.rev.c5.name': 'Zara Siddiqui',
      'idx.rev.c5.city': 'Rawalpindi',
      'idx.rev.c6.q': '"Been using the 3-in-1 kit for two months. Best investment I made for my hair. Friends keep asking what I use — it is all Nasir Oil Expert!"',
      'idx.rev.c6.name': 'Hamza Raza',
      'idx.rev.c6.city': 'Multan',

      /* Golden Oil page reviews */
      'go.rev.c1.name': 'Sarah Ahsan',
      'go.rev.c1.loc': 'Lahore, Pakistan',
      'go.rev.c1.q': '"Golden Oil completely transformed my hair! In just 4 weeks my hair became so thick and shiny. I used to lose so much hair while combing but now it\'s 70% less. I will never go back to chemical products."',
      'go.rev.c2.name': 'Nadia Khan',
      'go.rev.c2.loc': 'Karachi, Pakistan',
      'go.rev.c2.q': '"I\'ve tried so many oils but nothing worked like this. My hair was very dry and breaking at the ends. After just 3 weeks of regular use it feels completely different. The fragrance is also lovely and natural."',
      'go.rev.c3.name': 'Ayesha Mirza',
      'go.rev.c3.loc': 'Islamabad, Pakistan',
      'go.rev.c3.q': '"My mother has been using it for her dry scalp and can\'t stop praising it. She says it\'s like the pure oils of old times. We ordered 3 bottles this time so we don\'t run out!"',
      'go.rev.c4.name': 'Zara Baig',
      'go.rev.c4.loc': 'Faisalabad, Pakistan',
      'go.rev.c4.q': '"Fast delivery and exactly as described. Packaging is clean and the oil feels very premium. I apply it one night before washing and my hair has never been this soft. Highly recommend!"',

      /* Black Rose Oil page reviews */
      'br.rev.c1.name': 'Fatima Malik',
      'br.rev.c1.loc': 'Karachi, Pakistan',
      'br.rev.c1.q': '"The Black Rose Oil fragrance is amazing and it works even better than it smells! My hair fall reduced a lot in just 3 weeks. Now everyone asks what I put in my hair. Absolutely love it!"',
      'br.rev.c2.name': 'Rabia Ali',
      'br.rev.c2.loc': 'Multan, Pakistan',
      'br.rev.c2.q': '"I was hesitant because I had tried so many oils but this one is different. The scent is very light and natural. After use my hair feels very soft and strong. Will definitely order again!"',
      'br.rev.c3.name': 'Sana Hamid',
      'br.rev.c3.loc': 'Lahore, Pakistan',
      'br.rev.c3.q': '"Beautiful packaging and the oil is wonderful too. I have dyed hair and was worried about a reaction but my hair became even more beautiful. The shine is unbelievable. 5 stars!"',
      'br.rev.c4.name': 'Meera Nasir',
      'br.rev.c4.loc': 'Rawalpindi, Pakistan',
      'br.rev.c4.q': '"I gifted this to my sister and she loved it so much she ordered 3 more bottles. The rose fragrance is authentic and not too strong. It has become an essential in our home now."',

      /* Herbal Shampoo page reviews */
      'sh.rev.c1.q': '"Finally a shampoo that doesn\'t dry out my hair! My scalp is so clean and my hair is unbelievably soft. The herbal fragrance is very refreshing. Using it with Golden Oil and my hair has never felt this good."',
      'sh.rev.c2.q': '"Years of dandruff problem finally solved! In just two weeks my scalp became clean and itch-free. Great lather and cleans really well. Worth every penny."',
      'sh.rev.c3.q': '"I was hesitant to give up branded shampoo but this herbal one is so much better! My hair is growing faster, stronger and the colour looks healthier than ever. A must-have!"',
      'sh.rev.c4.q': '"The best natural shampoo with a lovely herbal fragrance. My hair doesn\'t feel dry at all after washing. The combination with Black Rose Oil is amazing. Delivery was also fast — arrived in 2 days!"',

      /* Index reels section */
      'idx.reel.label': 'Real Results',
      'idx.reel.h2': 'Watch Customer Stories',
      'idx.reel.p': 'See what our customers are saying on social media.',
      'idx.reel.c1': '"Incredible results in just 45 days!"',
      'idx.reel.c2': '"Hair fall stopped completely!"',
      'idx.reel.c3': '"My greying hair is back to black!"',
      'idx.reel.c4': '"Best herbal oil in Pakistan!"',

      /* Index review links */
      'rev.viewAll': 'View All Reviews & Videos',
      'rev.seeAll': 'See All Reviews',
      'prod.viewDetails': 'View Details',

      /* Products page */
      'pg.h1': 'Premium Hair Care',
      'pg.filter.kit': '3 in 1 Kit',
      'pg.filter.oils': 'Hair Oils',
      'pg.filter.shampoos': 'Shampoos',
      'pg.why.title': 'The Nasir Oil Expert Difference',
      'pg.why.best.desc': 'Premium quality at affordable prices. Natural ingredients, proven results.',
      'pg.cta.label': 'Ready to Order?',
      'pg.cta.title': 'Start Your Hair Care Journey Today',
      'pg.cta.sub': 'Add products to cart and checkout, or contact us directly on WhatsApp.',
      'pg.cta.cart': 'View Cart →',
      'pg.cta.wa': 'WhatsApp Order',
      'pg.bun.b1': '45-day complete treatment plan',
      'pg.bun.b2': 'Fights hair fall & dandruff',
      'pg.bun.b3': 'Reduces greying naturally',
      'pg.bun.b4': 'Home delivery available',
      'pg.bun.b5': '100% natural ingredients',

      /* About page */
      'ab.label': 'Our Story',
      'ab.h1': 'About Nasir Oil Expert',
      'ab.who.label': 'Who We Are',
      'ab.who.title': 'Rooted in Nature, Committed to Excellence',
      'ab.stat1': 'Happy Customers',
      'ab.stat2': 'Product Range',
      'ab.stat3': 'Natural Ingredients',
      'ab.stat4': 'Avg. Rating',
      'ab.val1.title': 'Purity',
      'ab.val2.title': 'Trust',
      'ab.val3.title': 'Sustainability',
      'ab.val4.title': 'Innovation',
      'ab.val5.title': 'Care',
      'ab.val6.title': 'Excellence',
      'ab.tl1.title': 'The Beginning',
      'ab.tl2.title': 'First Product Launch',
      'ab.tl3.title': 'Expanding the Range',
      'ab.tl4.title': '5000+ Happy Customers',
      'ab.cta.label': 'Get Started',
      'ab.cta.title': 'Ready to Experience the Difference?',
      'ab.cta.shop': 'Shop Now →',
      'ab.cta.contact': 'Contact Us',

      /* Reviews page */
      'rv.verified.label': 'Verified Reviews',
      'rv.h1': 'Customer Reviews & Stories',
      'rv.based': 'Based on 200+ reviews',
      'rv.saying.title': 'What Customers Are Saying',
      'rv.video.label': 'Video Testimonials',
      'rv.video.title': 'Customer Reels',
      'rv.video.sub': 'Watch our customers share their real results.',
      'rv.cta.title': 'Ready to See Results?',
      'rv.cta.sub': 'Join thousands of satisfied customers across Pakistan.',
      'rv.cta.shop': 'Shop Now',
      'rv.cta.wa': 'Order on WhatsApp',
      'rv.c1.q': '"After just 45 days of using the complete kit, my hair fall reduced by 80%. The Black Rose Oil works overnight. I wake up to softer, thicker hair."',
      'rv.c2.q': '"Nasir Golden Oil has been a game changer. My hair went from dry and brittle to shiny and strong in one month. 100% natural, you can feel the difference."',
      'rv.c3.q': '"The Herbal Shampoo is so gentle, no dryness, no frizz. My dandruff is completely gone after using it with the Golden Oil for 3 weeks. Highly recommended!"',
      'rv.c4.q': '"I was losing hair rapidly after my pregnancy. A friend recommended Black Rose Oil and within 6 weeks my hair came back thicker than before. Amazing product!"',
      'rv.c5.q': '"Ordered the 3-in-1 bundle and followed the 45-day plan exactly. My grey hairs are reducing, dandruff gone, and hair is growing faster. Worth every rupee!"',
      'rv.c6.q': '"Best oil I have ever used. No chemicals, no side effects. My scalp feels healthy and my hair smells fresh all day. Will keep ordering for my whole family."',
      'rv.c1.name': 'Ayesha Khan',
      'rv.c2.name': 'Muhammad Usman',
      'rv.c3.name': 'Sara Mahmood',
      'rv.c4.name': 'Fatima Siddiqui',
      'rv.c5.name': 'Hassan Ali',
      'rv.c6.name': 'Zainab Raza',
      'rv.c1.loc': 'Lahore · Verified Purchase',
      'rv.c2.loc': 'Karachi · Verified Purchase',
      'rv.c3.loc': 'Islamabad · Verified Purchase',
      'rv.c4.loc': 'Rawalpindi · Verified Purchase',
      'rv.c5.loc': 'Faisalabad · Verified Purchase',
      'rv.c6.loc': 'Multan · Verified Purchase',
      'rv.c5.prod': '3-in-1 Kit',
      'rv.vq1': '"Incredible results in just 45 days!"',
      'rv.vq2': '"Hair fall stopped completely!"',
      'rv.vq3': '"My greying hair is back to black!"',
      'rv.vq4': '"Best herbal oil in Pakistan!"',

      /* Contact page headings */
      'ct.reach.label': 'Reach Out to Us',
      'ct.reach.title': 'Get In Touch',
      'ct.reach.sub': 'Have a question, want to place an order or need support? We\'re here to help.',
      'ct.phone.title': 'Phone / Call',
      'ct.wa.h': 'WhatsApp Order',
      'ct.email.h': 'Email',
      'ct.store.h': 'Our Store',
      'ct.social.label': 'Social Media',
      'ct.form.title': 'Send Us Your Message',
      'ct.wa.direct': 'Message Us Directly on WhatsApp',
      'ct.form.or': 'or fill in the form below',
      'ct.email.label': 'Email',
      'ct.subject.label': 'Subject',
      'ct.maps.label': 'Our Location',
      'ct.maps.title': 'Find Us on the Map',
      'ct.maps.btn': 'Open in Google Maps',
      'ct.faq.label': 'FAQ',
      'ct.faq.title': 'Frequently Asked Questions',
      'ct.faq.sub': 'Find answers to our most commonly asked questions.',

      /* Products page h2 */
      'pg.essentials.h2': 'Choose Your Hair Care Essential',
      'pg.why.label': 'Why Choose Us',
      'pg.why.value.title': 'Best Value',
      'pg.why.value.desc': 'Premium quality at affordable prices that fit every budget.',
      'pg.bundles.label': 'Special Deals',
      'pg.bundles.h2': 'Value Combo Packs',
      'pg.bundles.sub': 'Get the complete hair care experience with our specially curated combo packs at unbeatable prices.',
      'pg.combo1.name': 'Golden Oil + Herbal Shampoo',
      'pg.combo1.desc': 'The perfect everyday hair care duo. Cleanse with our herbal shampoo and nourish deeply with Golden Oil for maximum hair health.',
      'pg.combo2.name': 'Black Rose Oil + Herbal Shampoo',
      'pg.combo2.desc': 'A luxurious combination for premium fragrance and strength. The ultimate hair care experience for silky, strong hair.',
      'pg.bundle.save': 'Save Rs 200',

      /* About page story + value + timeline content */
      'ab.who.p1': 'Nasir Oil Expert was born from a deep belief in the healing power of nature. Founded with a mission to make authentic, pure hair oils accessible to everyone in Pakistan, we have spent years perfecting our formulations using time-honored botanical traditions.',
      'ab.who.p2': 'Every product we create is a labour of love — carefully sourced, cold-pressed, and blended without synthetic chemicals. We believe your hair deserves the very best that nature has to offer.',
      'ab.val1.desc': 'We use only the finest natural ingredients with no synthetic additives or harsh chemicals. What you see on the label is exactly what\'s in the bottle.',
      'ab.val2.desc': 'We build lasting relationships with our customers through transparency, honest communication, and consistent product quality they can rely on.',
      'ab.val3.desc': 'We source our botanicals responsibly and are committed to eco-friendly practices that protect the environment for future generations.',
      'ab.val4.desc': 'We continuously research and improve our formulations, combining ancient herbal wisdom with modern understanding of hair science.',
      'ab.val5.desc': 'Every product is made with genuine care for our customers\' well-being. We treat your hair as if it were our own — with the utmost attention and love.',
      'ab.val6.desc': 'We never compromise on quality. Each batch undergoes rigorous quality checks to ensure you receive a product that truly delivers results.',
      'ab.tl1.desc': 'Nasir Oil Expert was founded with a single mission: to provide pure, authentic hair oils that actually work. Started from humble roots with traditional recipes.',
      'ab.tl2.desc': 'Launched our signature Golden Oil — the product that started it all. Received overwhelming response and quickly became a customer favourite.',
      'ab.tl3.desc': 'Added Black Rose Oil and Herbal Shampoo to our lineup, completing a full natural hair care system that customers could trust.',
      'ab.tl4.desc': 'Today we proudly serve thousands of customers across Pakistan, and our commitment to quality and authenticity remains as strong as ever.',
      'ab.cta.p': 'Join thousands of happy customers who have transformed their hair with Nasir Oil Expert.',

      /* Contact intro paragraph */
      'ct.reach.p': 'Have a question, want to place an order, or need help? We are here for you. WhatsApp or call us and we will get back to you as soon as possible.',

      /* Contact page hero + breadcrumb */
      'ct.hero.label': 'Get In Touch',
      'ct.hero.h1': 'Contact Nasir Oil Expert',
      'bc.contact': 'Contact',

      /* Contact form labels */
      'ct.form.name.label': 'Name',
      'ct.form.phone.label': 'Phone',
      'ct.form.msg.label': 'Message',

      /* Contact form placeholders */
      'ct.form.name.ph': 'Your name',
      'ct.form.phone.ph': '03XX-XXXXXXX',
      'ct.form.email.ph': 'email@example.com',
      'ct.form.msg.ph': 'Type your message or order details here...',

      /* Contact form select options */
      'ct.subject.default': 'Please select...',
      'ct.subject.order': 'Place an Order',
      'ct.subject.delivery': 'Delivery Inquiry',
      'ct.subject.product': 'Product Information',
      'ct.subject.return': 'Return / Refund',
      'ct.subject.other': 'Something Else',

      /* FAQ questions & answers */
      'ct.faq.q1': 'How long does delivery take?',
      'ct.faq.a1': 'Delivery takes 2 to 4 working days in most cities across Pakistan. Remote areas may take 5 to 7 days.',
      'ct.faq.q2': 'Is Cash on Delivery (COD) available?',
      'ct.faq.a2': 'Yes! We offer Cash on Delivery across Pakistan. You pay at home when your parcel arrives — no advance payment needed.',
      'ct.faq.q3': 'What are the delivery charges?',
      'ct.faq.a3': 'Delivery is completely free on orders over Rs 1500. For orders below Rs 1500, a delivery charge of Rs 150 applies.',
      'ct.faq.q4': 'Are the products 100% natural?',
      'ct.faq.a4': 'Absolutely! All our products are made exclusively from natural botanical ingredients. No synthetic chemicals or artificial fragrances are used.',
      'ct.faq.q5': 'How do I cancel or return an order?',
      'ct.faq.a5': 'To cancel or return an order, simply WhatsApp or call us. We will assist you fully and make the process as smooth as possible.',
      'ct.faq.q6': 'Is there a discount on bulk orders?',
      'ct.faq.a6': 'Yes! Special discounts are available on orders of 5 or more bottles. Contact us for more details and we will arrange the best deal for you.',

      /* Products page bullet items */
      'pg.golden.b1': 'Promotes faster hair growth',
      'pg.golden.b2': 'Reduces hair fall & breakage',
      'pg.golden.b3': 'Adds brilliant natural shine',
      'pg.golden.b4': 'Deeply moisturizes dry scalp',
      'pg.golden.b5': '100% natural, no parabens',
      'pg.rose.b1': 'Strengthens hair follicles',
      'pg.rose.b2': 'Fights frizz & flyaways',
      'pg.rose.b3': 'Long-lasting natural fragrance',
      'pg.rose.b4': 'Antioxidant protection',
      'pg.rose.b5': 'Makes hair silky smooth',
      'pg.shampoo.b1': 'Sulfate & paraben free',
      'pg.shampoo.b2': 'Soothes itchy scalp',
      'pg.shampoo.b3': 'Controls dandruff naturally',
      'pg.shampoo.b4': 'Safe for all hair types',
      'pg.shampoo.b5': 'Fresh herbal fragrance',

      /* Bundle Kit product detail page */
      'bk.save.badge': 'You Save Rs 750 — Best Bundle Deal',
      'bk.desc': 'Everything your hair needs in one kit. Our 3-in-1 Complete Hair Kit combines Golden Oil, Black Rose Oil, and Herbal Shampoo into a complete 45-day treatment that fights hair fall, reduces greying, eliminates dandruff, and restores natural shine from root to tip.',
      'bk.kit.golden.desc': 'Deep nourishment &amp; hair growth boost',
      'bk.kit.rose.desc': 'Strength, shine &amp; anti-breakage formula',
      'bk.kit.shampoo.desc': 'Sulfate-free botanical cleanser',
      'bk.trust.delivery': 'Rs 250 delivery • Free on Rs 5,000+',
      'bk.trust.cod': 'Cash on Delivery',
      'bk.inside.p1': 'The 3-in-1 Complete Hair Kit contains all three of our core products, carefully packaged together for the complete Nasir Oil Expert 45-day hair treatment program.',
      'bk.inside.p2': '<strong>Golden Hair Oil</strong> — Our signature cold-pressed blend of mustard, coconut, sesame and almond oils enriched with herbal extracts. Deeply nourishes from root to tip, adds brilliant shine and promotes faster hair growth.',
      'bk.inside.p3': '<strong>Black Rose Oil</strong> — Infused with rare black rose extract and luxurious botanical oils. Strengthens hair from root to tip, protects against environmental damage, and leaves hair with a subtle, long-lasting fragrance.',
      'bk.inside.p4': '<strong>Herbal Shampoo</strong> — Formulated with neem, amla, bhringraj and botanical extracts. Sulfate-free and gentle — cleanses without stripping natural oils, leaving hair soft and manageable after every wash.',
      'bk.ben.1': 'Reduces hair fall by up to 70%',
      'bk.ben.2': 'Prevents premature greying naturally',
      'bk.ben.3': 'Eliminates dandruff from roots',
      'bk.ben.4': 'Strengthens hair follicles deeply',
      'bk.ben.5': 'Adds brilliant natural shine',
      'bk.ben.6': 'Repairs dry and damaged hair',
      'bk.ben.7': 'Promotes faster new hair growth',
      'bk.ben.8': 'Soothes dry and itchy scalp',
      'bk.ben.9': 'Fights frizz and flyaways',
      'bk.ben.10': 'Long-lasting herbal fragrance',
      'bk.howto.plan': 'Complete 45-day Treatment Plan',
      'bk.howto.p1': 'Apply Black Rose Oil to hair roots every night for 10 consecutive nights. Massage gently into the scalp. Wash hair with Nasir Herbal Shampoo each morning.',
      'bk.howto.p2': 'Apply Golden Oil to hair roots and massage gently into the scalp. Leave overnight and wash with Nasir Herbal Shampoo the next morning.',
      'bk.howto.p3': 'Continue alternating Black Rose Oil and Golden Oil daily. Wash with Nasir Herbal Shampoo each time to keep hair clean and fresh.',
      'bk.howto.note': '<strong>Note:</strong> After 45 days, alternate Black Rose Oil and Golden Oil in 5-day cycles. Continue for 40–45 days for full results. Best results seen with consistent daily use.',
      'bk.why.p1': 'Buying the kit instead of individual products gives you a Rs 750 saving right away — but the real value is in having the complete treatment system from day one, without needing to order anything extra.',
      'bk.why.p2': 'Our 45-day treatment plan requires all three products working together. When used as a system, they complement each other perfectly — the oils treat the scalp while the shampoo cleanses without undoing the nourishment.',
      'bk.why.p3': 'Customers who start with the full kit see significantly better results compared to those who use only one product, because the treatment addresses hair fall, greying and dandruff simultaneously rather than one at a time.',
      'bk.why.saved': 'Saved vs. buying separately',
      'bk.why.complete': 'Complete treatment program',
      'bk.why.system': 'One complete hair care system',
      'bk.rev.c1.name': 'Hana Farooq',
      'bk.rev.c1.loc': 'Lahore, Pakistan',
      'bk.rev.c1.q': '"I was skeptical at first but the results after the 45-day plan were unbelievable. My hair fall has reduced so much and my hair is visibly thicker. The shampoo is also amazing — no dryness after washing. Ordering again for sure."',
      'bk.rev.c2.name': 'Rabab Ali',
      'bk.rev.c2.loc': 'Karachi, Pakistan',
      'bk.rev.c2.q': '"The kit is completely worth it. Rs 750 savings is a bonus but honestly even at full price I would have bought it. The three products work perfectly together. My grey hairs have reduced and my hair is growing faster than before."',
      'bk.rev.c3.name': 'Sana Malik',
      'bk.rev.c3.loc': 'Islamabad, Pakistan',
      'bk.rev.c3.q': '"Bought for my mother who had severe hair fall. After following the 45-day plan, she noticed a big difference. Her hair feels much stronger and she has not had dandruff since starting the shampoo. The Black Rose Oil smell is also lovely."',
      'bk.rev.c4.name': 'Tahrim Khan',
      'bk.rev.c4.loc': 'Faisalabad, Pakistan',
      'bk.rev.c4.q': '"Fast delivery, good packaging. I followed the instructions exactly and by day 15 my hair felt noticeably different. Soft, shiny and less breakage. The Golden Oil is my favourite — it smells natural and absorbs quickly. Highly recommend the kit!"',

      /* About page badge + mission quote */
      'ab.badge.since': 'Since<br>1948',
      'ab.badge.years': '78+ Years of<br>Excellence',
      'ab.mission.quote': '"To bring the <span>purest, most authentic</span> natural hair oils to every household, empowering people to embrace nature\'s wisdom for healthier, more beautiful hair."',

      /* Product page main descriptions */
      'pd.golden.desc': 'Our signature Golden Hair Oil is a luxurious cold-pressed blend of the finest natural oils — mustard, coconut, sesame and almond — enriched with herbal extracts. It deeply nourishes from root to tip, adds brilliant shine, promotes faster hair growth and significantly reduces hair fall.',
      'pd.rose.desc': 'Infused with rare black rose extract, this premium hair oil combines the timeless elegance of rose with the deep nourishing power of cold-pressed natural oils. It strengthens hair follicles from within, dramatically reduces breakage, adds a stunning silky shine and leaves your hair with a delicate, long-lasting natural fragrance.',
      'pd.shampoo.desc': 'Our Herbal Shampoo is the perfect companion to our premium oils. Formulated with a powerful blend of neem, amla, bhringraj, and other botanical extracts, it gently cleanses your scalp and hair without stripping away natural oils. Completely sulfate and paraben free — safe for daily use, color-treated hair, and all hair types.',

      /* How to Use phase headings (shared across product pages) */
      'pd.phase1.h4': 'Phase 1 — Black Rose Oil (10 Nights)',
      'pd.phase2.h4': 'Phase 2 — Golden Oil (5 Days)',
      'pd.phase3.h4': 'Phase 3 — Alternating Oils (5 Days)',

      /* Ingredients — Golden Oil */
      'pd.ing.mustard': 'Mustard Oil',
      'pd.ing.coconut': 'Coconut Oil',
      'pd.ing.sesame': 'Sesame Oil',
      'pd.ing.almond': 'Almond Oil',
      'pd.ing.amla': 'Amla Extract',
      'pd.ing.bhringraj': 'Bhringraj',

      /* Ingredients — Black Rose Oil */
      'pd.ing.blackrose': 'Black Rose Extract',
      'pd.ing.argan': 'Argan Oil',
      'pd.ing.castor': 'Castor Oil',
      'pd.ing.rosehip': 'Rose Hip Oil',
      'pd.ing.vitE': 'Vitamin E',

      /* Ingredients — Herbal Shampoo */
      'pd.ing.neem': 'Neem Extract',
      'pd.ing.amlaberry': 'Amla (Indian Gooseberry)',
      'pd.ing.coconutmilk': 'Coconut Milk',
      'pd.ing.chamomile': 'Chamomile Extract',
      'pd.ing.aloe': 'Aloe Vera Gel',
      'pd.badge.sulfate': 'Sulfate Free',

      /* Cart badge */
      'tag.new': 'New',
    },

    ur: {
      /* Navigation */
      'nav.home': 'گھر',
      'nav.collections': 'کلیکشن',
      'nav.about': 'ہمارے بارے میں',
      'nav.reviews': 'تبصرے',
      'nav.contact': 'رابطہ',
      'nav.bundleKit': 'بنڈل کٹ',
      'nav.goldenOil': 'گولڈن آئل',
      'nav.blackRoseOil': 'بلیک روز آئل',
      'nav.herbalShampoo': 'ہربل شیمپو',
      'nav.orderNow': 'ابھی آرڈر کریں',
      'nav.cart': '🛒 کارٹ',

      /* Hero */
      'hero.since': '1948 سے · ناصر آئل ایکسپرٹ',
      'hero.h1': 'خالص اور اصل<br><span class="highlight">بالوں کی دیکھ بھال</span>',
      'hero.sub': 'قدرتی تیل اور جڑی بوٹیوں سے بنی نگہداشت — بالوں کو مضبوط، چمکدار اور صحت مند بناتی ہے۔',
      'hero.chip1': '100% قدرتی',
      'hero.chip2': '1948 سے',
      'hero.chip3': '5000+ گاہک',
      'hero.chip4': 'ملک گیر ڈیلیوری',

      /* Shop / Products section */
      'shop.label': 'ہماری کلیکشن',
      'shop.title': 'کلیکشن سے خریدیں',
      'shop.sub': 'تمام مصنوعات 100% قدرتی اجزاء سے بنائی گئی ہیں۔',
      'filter.all': 'تمام مصنوعات',
      'filter.oil': 'گولڈن آئل',
      'filter.rose': 'بلیک روز آئل',
      'filter.shampoo': 'ہربل شیمپو',

      /* Product tags */
      'tag.bestValue': 'بہترین قیمت',
      'tag.bestSeller': 'سب سے زیادہ فروخت',
      'tag.premium': 'پریمیم',
      'tag.natural': 'قدرتی',

      /* Product names */
      'prod.bundle': '3 میں 1 مکمل کٹ',
      'prod.golden': 'گولڈن آئل',
      'prod.rose': 'بلیک روز آئل',
      'prod.shampoo': 'ہربل شیمپو',

      /* Benefit chips */
      'ben.golden': '✦ گولڈن آئل',
      'ben.rose': '✦ بلیک روز آئل',
      'ben.shampoo': '✦ ہربل شیمپو',
      'ben.growth': '✦ بالوں کی نشوونما',
      'ben.nourish': '✦ گہری غذائیت',
      'ben.shine': '✦ چمک میں اضافہ',
      'ben.strong': '✦ مضبوطی',
      'ben.nobreak': '✦ ٹوٹنے سے حفاظت',
      'ben.fragrant': '✦ خوشبودار',
      'ben.cleanse': '✦ نرم صفائی',
      'ben.scalp': '✦ کھوپڑی کی دیکھ بھال',
      'ben.sulfate': '✦ سلفیٹ فری',

      /* Descriptions */
      'desc.bundle': 'مکمل 45 دن کا علاج — بال گرنا، سفیدی اور خشکی سے لڑتا ہے۔ تینوں مصنوعات ایک خاص کٹ میں۔ Rs 750 کی بچت۔',
      'desc.golden': 'ہمارا خاص گولڈن آئل پریمیم کولڈ پریسڈ تیلوں اور جڑی بوٹیوں کا امتزاج ہے۔ بالوں کی جڑوں تک پہنچ کر نمی، غذائیت اور صحت مند نشوونما دیتا ہے۔',
      'desc.rose': 'نایاب کالے گلاب کے عرق اور بوٹانیکل تیلوں سے بنا۔ جڑ سے سرے تک مضبوطی، دیرپا خوشبو اور اینٹی آکسیڈنٹ تحفظ۔',
      'desc.shampoo': 'نیم، آملہ، بھرنگراج اور بوٹانیکل عرق سے بنا۔ قدرتی تیلوں کو برقرار رکھتے ہوئے نرمی سے صفائی۔ ہر دھونے میں نرم اور خوبصورت بال۔',

      /* Buttons */
      'btn.cart': '🛒 کارٹ میں شامل کریں',
      'btn.details': 'تفصیل دیکھیں',
      'btn.wa': '📲 واٹس ایپ پر آرڈر کریں',
      'btn.orderNow': 'ابھی آرڈر کریں',

      /* Bundle section */
      'bund.label': 'خصوصی ڈیل',
      'bund.title': 'مکمل بالوں کا علاج کٹ',
      'bund.badge': '3 میں 1 بنڈل',
      'bund.items': 'گولڈن آئل + بلیک روز آئل + ہربل شیمپو',
      'bund.save': 'Rs 750 کی بچت',
      'bund.oldPrice': 'Rs 4,000 علیحدہ',

      /* Why choose us */
      'why.label': 'ہمیں کیوں چنیں',
      'why.title': 'ناصر کا فرق',
      'why.sub': 'چھ دہائیوں کی مہارت ہر بوتل میں۔',
      'why.f1': '100% قدرتی',
      'why.f1d': 'کوئی نقصاندہ کیمیکل، مصنوعی خوشبو یا پریزرویٹو نہیں',
      'why.f2': 'کولڈ پریسڈ',
      'why.f2d': 'ہمارا خاص طریقہ ہر غذائیت کو محفوظ رکھتا ہے',
      'why.f3': 'اصل نسخہ',
      'why.f3d': '1948 کا اصل فارمولہ، نسلوں سے قابل اعتماد',
      'why.f4': 'ڈیلیوری',
      'why.f4d': 'آپ کے دروازے تک تیز، ملک گیر شپنگ',

      /* Reels & Testimonials */
      'reels.label': 'کسٹمر ریلز',
      'reels.title': 'اصل تبدیلیاں دیکھیں',
      'test.label': 'تبصرے',
      'test.title': 'اصل لوگ۔ اصل نتائج۔',

      /* Products page */
      'pg.prod.label': 'ہماری کلیکشن',
      'pg.prod.title': 'تمام مصنوعات',
      'pg.prod.sub': 'قدرتی بالوں کی دیکھ بھال کی مکمل رینج',

      /* Product detail — shared */
      'pd.badge.best': 'سب سے زیادہ فروخت',
      'pd.badge.natural': '100% قدرتی',
      'pd.badge.premium': 'پریمیم',
      'pd.badge.herbal': 'جڑی بوٹی',
      'pd.tab.desc': 'تفصیل',
      'pd.tab.benefits': 'فوائد',
      'pd.tab.ing': 'اجزاء',
      'pd.tab.use': 'استعمال کا طریقہ',
      'pd.tab.rev': 'تبصرے',
      'pd.delivery': '🚚 پورے ملک میں مفت ڈیلیوری',
      'pd.instock': '✓ دستیاب ہے',
      'pd.natural': '100% قدرتی اور تصدیق شدہ',
      'pd.addCart': '🛒 کارٹ میں شامل کریں',
      'pd.wa': '📲 واٹس ایپ پر آرڈر کریں',

      /* Product detail — Golden Oil */
      'pd.golden.name': 'گولڈن ہیئر آئل',
      'pd.golden.sub': 'پریمیم کولڈ پریسڈ بالوں کی نشوونما کا تیل',

      /* Product detail — Black Rose Oil */
      'pd.rose.name': 'بلیک روز آئل',
      'pd.rose.sub': 'لگژری بوٹانیکل بالوں کو مضبوط کرنے والا تیل',

      /* Product detail — Herbal Shampoo */
      'pd.shampoo.name': 'ہربل شیمپو',
      'pd.shampoo.sub': 'قدرتی جڑی بوٹیوں سے بنا شیمپو',

      /* Product detail — Bundle Kit */
      'pd.bundle.name': '3 میں 1 مکمل ہیئر کٹ',
      'pd.bundle.sub': 'گولڈن آئل + بلیک روز آئل + ہربل شیمپو',

      /* About page */
      'ab.mission.label': 'ہمارا مشن',
      'ab.values.label': 'ہماری اقدار',
      'ab.values.title': 'ہم کس کے لیے کھڑے ہیں',
      'ab.values.sub': 'یہ بنیادی اقدار ہر کام میں ہماری رہنمائی کرتی ہیں۔',
      'ab.journey.label': 'ہمارا سفر',
      'ab.journey.title': 'یہ سب کیسے شروع ہوا',
      'ab.journey.sub': 'ایک چھوٹے خواب سے ہزاروں خوش گاہکوں تک — ہماری کہانی۔',

      /* Contact page */
      'ct.label': 'ہم سے رابطہ کریں',
      'ct.title': 'رابطہ کریں',
      'ct.sub': 'آرڈر دیں یا کوئی سوال پوچھیں۔',

      /* Reviews page */
      'rv.label': 'گاہکوں کے تبصرے',
      'rv.title': 'ہمارے گاہک کیا کہتے ہیں',

      /* Bundle deal content */
      'bund.badge2': '3 میں 1 بنڈل',
      'bund.kit.title': 'ناصر ہربل ہیئر مکمل کٹ',
      'bund.kit.desc': '45 دن کا مکمل علاج — بال گرنا، سفیدی، خشکی اور نشوونما کے لیے۔ مکمل 3 مرحلہ علاج کی تینوں مصنوعات ایک کٹ میں۔',
      'bund.chip1': 'گولڈن آئل',
      'bund.chip2': 'ہربل شیمپو',
      'bund.chip3': 'بلیک روز آئل',
      'bund.btn': '🛒 کارٹ میں شامل کریں',
      'bund.viewDetails': 'تفصیل دیکھیں →',

      /* Why Choose Us cards */
      'why.c1.title': '100% قدرتی',
      'why.c1.desc': 'کوئی مصنوعی کیمیکل نہیں۔ ہر قطرے میں خالص نباتاتی اچھائی۔',
      'why.c2.title': '1948 سے',
      'why.c2.desc': 'اصل جڑی بوٹیوں کی بالوں کی دیکھ بھال میں 78 سال سے زیادہ کا تجربہ۔',
      'why.c3.title': 'تیز ڈیلیوری',
      'why.c3.desc': 'پاکستان بھر میں آپ کے دروازے تک تیز اور قابل اعتماد ڈیلیوری۔',
      'why.c4.title': '5000+ گاہک',
      'why.c4.desc': 'پاکستان بھر میں ہزاروں مطمئن گاہکوں کا اعتماد۔',

      /* Customer reviews section */
      'rev.label': 'گاہکوں کے تبصرے',
      'rev.title': 'ہمارے گاہک کیا کہتے ہیں',
      'rev.sub': 'پاکستان بھر کے اصل لوگوں کے اصل نتائج۔',

      /* Footer */
      'ft.tagline': 'خالص اور اصل تیل',
      'ft.brand.desc': '1948 سے خالص اور اصل بالوں کے تیل۔ پاکستان بھر میں ہزاروں کا اعتماد۔',
      'ft.quickLinks': 'فوری لنکس',
      'ft.products': 'مصنوعات',
      'ft.contact': 'رابطہ',
      'ft.home': 'گھر',
      'ft.collections': 'کلیکشن',
      'ft.reviews': 'تبصرے',
      'ft.about': 'ہمارے بارے میں',
      'ft.contactLink': 'رابطہ',
      'ft.waOrder': 'واٹس ایپ آرڈر',
      'ft.rights': '© 2026 ناصر آئل ایکسپرٹ۔ جملہ حقوق محفوظ ہیں۔',
      'ft.followUs': 'ہمیں فالو کریں',

      /* Products page */
      'pg.viewDetails': 'تفصیل دیکھیں',
      'pg.addCart': '🛒 کارٹ میں شامل کریں',

      /* Contact page */
      'ct.name': 'آپ کا نام',
      'ct.phone': 'فون نمبر',
      'ct.message': 'آپ کا پیغام',
      'ct.send': 'پیغام بھیجیں',
      'ct.wa.title': 'واٹس ایپ پر آرڈر کریں',
      'ct.wa.sub': 'واٹس ایپ کے ذریعے آسان آرڈر',
      'ct.wa.btn': 'واٹس ایپ پر چیٹ کریں',

      /* Product detail — WhatsApp button */
      'pd.wa.order': 'واٹس ایپ پر آرڈر کریں',
      'pd.cod.buy': '💵 کیش آن ڈیلیوری سے خریدیں',

      /* Cart page */
      'cart.title': 'آپ کا کارٹ',
      'cart.empty': 'آپ کا کارٹ خالی ہے',
      'cart.total': 'کل رقم',
      'cart.checkout': 'چیک آؤٹ کریں',
      'cart.continue': 'خریداری جاری رکھیں',
      'cart.section.label': 'آپ کا انتخاب',
      'cart.items': 'کارٹ میں چیزیں',
      'cart.cont': '← خریداری جاری رکھیں',
      'cart.summary': 'آرڈر کا خلاصہ',
      'cart.subtotal': 'ذیلی کل',
      'cart.delivery.label': 'ڈیلیوری',
      'cart.freedel': 'Rs 250 ڈیلیوری چارجز — Rs 5,000 سے زیادہ آرڈر پر مفت!',
      'cart.wa.btn': 'واٹس ایپ آرڈر',
      'cart.payment': 'ادائیگی کے طریقے',
      'cart.cod': '💵 کیش آن ڈیلیوری',
      'cart.more.label': 'مزید مصنوعات',
      'cart.more.title': 'آپ کو یہ بھی پسند آ سکتا ہے',
      'cart.h1': 'شاپنگ کارٹ',
      'bc.cart': 'کارٹ',
      'cart.golden.desc': 'خشک اور خراب بالوں کے لیے گہری غذائیت۔',
      'cart.rose.desc': 'چمک اور مضبوطی کے لیے لگژری گلاب کا عرق۔',
      'cart.shampoo.desc': 'قدرتی نباتاتی عرق سے نرم روزانہ صفائی۔',
      'cart.herbal.badge': 'جڑی بوٹی',

      /* Checkout page */
      'co.hero.label': 'محفوظ چیک آؤٹ',
      'co.h1': 'اپنا آرڈر دیں',
      'bc.checkout': 'چیک آؤٹ',
      'co.step.confirm': 'تصدیق',
      'co.delivery.title': 'ڈیلیوری کی معلومات',
      'co.name.label': 'پورا نام *',
      'co.phone.label': 'فون نمبر *',
      'co.city.label': 'شہر *',
      'co.city.select': 'اپنا شہر منتخب کریں',
      'co.address.label': 'مکمل پتہ *',
      'co.payment.title': 'ادائیگی کا طریقہ',
      'co.cod.name': 'کیش آن ڈیلیوری (COD)',
      'co.cod.sub': 'آرڈر ڈیلیور ہونے پر نقد ادائیگی کریں',
      'co.cod.info': 'کیش آن ڈیلیوری — آرڈر پہنچنے پر گھر پر ادائیگی کریں۔ پیشگی ادائیگی کی ضرورت نہیں۔',
      'co.freedel.p': '✅ Rs 250 ڈیلیوری چارجز — Rs 5,000 سے زیادہ آرڈر پر مفت!',
      'co.place.order': '🛒 آرڈر دیں',
      'co.wa.confirm': 'آرڈر دینے کے بعد آپ کو واٹس ایپ تصدیق ملے گی',
      'co.success.title': 'آرڈر دے دیا گیا!',
      'co.success.p1': 'شکریہ! آپ کا آرڈر موصول ہو گیا۔ آپ کو جلد واٹس ایپ تصدیق ملے گی۔',
      'co.success.p2': 'واٹس ایپ کھل رہا ہے — آپ کے آرڈر کی تفصیل خود بخود بھیجی جائے گی۔',
      'co.success.home': 'ہوم پر جائیں',
      'co.success.browse': 'مزید پروڈکٹس دیکھیں',
      'co.back.cart': '← کارٹ پر واپس',
      'co.secure.title': 'محفوظ اور قابل اعتماد',
      'co.secure.list': '🔒 آپ کی معلومات محفوظ ہے<br>🚚 پورے پاکستان میں تیز ڈیلیوری<br>💵 COD دستیاب ہے<br>🔄 آسان واپسی',

      /* Nav logo */
      'nav.logo.name': 'ناصر آئل ایکسپرٹ',

      /* Marquee items */
      'marq.1': 'خالص نکالے گئے تیل',
      'marq.2': 'نامیاتی اور جڑی بوٹی',
      'marq.3': 'گولڈن آئل کلیکشن',
      'marq.4': 'بلیک روز آئل',
      'marq.5': 'ہربل شیمپو',
      'marq.6': '100% قدرتی',
      'marq.7': '1948 سے',
      'marq.8': 'ملک گیر ڈیلیوری',
      'marq.9': 'اصل فارمولہ',

      /* Footer product links */
      'ft.prod.bundle': 'بنڈل کٹ',
      'ft.prod.golden': 'گولڈن آئل',
      'ft.prod.rose': 'بلیک روز آئل',
      'ft.prod.shampoo': 'ہربل شیمپو',
      'ft.prod.combos': 'کمبو ڈیلز',

      /* Breadcrumbs */
      'bc.home': 'گھر',
      'bc.products': 'مصنوعات',
      'bc.about': 'بارے میں',
      'bc.golden': 'گولڈن ہیئر آئل',
      'bc.rose': 'بلیک روز ہیئر آئل',
      'bc.shampoo': 'ہربل شیمپو',
      'bc.bundle': '3 میں 1 مکمل کٹ',

      /* Product perks */
      'pd.perk.delivery': 'Rs 250 ڈیلیوری • Rs 5,000+ پر مفت',
      'pd.perk.cod': 'کیش آن ڈیلیوری',
      'pd.perk.natural100': '100% قدرتی',
      'pd.perk.rose.infused': 'گلاب سے بھرپور',
      'pd.perk.botanicals': '100% قدرتی نباتات',
      'pd.perk.sulfate': 'سلفیٹ اور پیرابین فری',
      'pd.perk.allhair': 'تمام بالوں کے لیے محفوظ',

      /* Tab buttons */
      'pd.tab.inside': 'اندر کیا ہے',
      'pd.tab.why': 'بنڈل کیوں؟',

      /* Product review section */
      'pd.rev.label': 'گاہکوں کے تبصرے',
      'pd.rev.title': 'لوگ کیا کہہ رہے ہیں',
      'pd.rev.verified': '✓ تصدیق شدہ خریداری',
      'pd.rev.128': '128 تبصروں پر مبنی',
      'pd.rev.96': '96 تبصروں پر مبنی',
      'pd.rev.87': '87 تبصروں پر مبنی',
      'pd.rev.214': '214 تبصروں پر مبنی',

      /* Related / included products */
      'pd.rel.label': 'آپ کو یہ بھی پسند آ سکتا ہے',
      'pd.rel.title': 'متعلقہ مصنوعات',
      'pd.kit.label': 'اس کٹ میں شامل',
      'pd.kit.title': 'انفرادی مصنوعات خریدیں',
      'pd.bundle.addcart': 'کارٹ میں شامل کریں — Rs 3,250',

      /* Index review cards */
      'idx.rev.c1.q': '"صرف 45 دن مکمل کٹ استعمال کرنے کے بعد، میرے بال گرنے میں 80٪ کمی آئی۔ بلیک روز آئل رات بھر اثر کرتا ہے۔ میں نرم اور گھنے بالوں کے ساتھ اٹھتی ہوں۔"',
      'idx.rev.c1.name': 'Ayesha Khan',
      'idx.rev.c1.city': 'لاہور',
      'idx.rev.c2.q': '"ناصر گولڈن آئل بہترین پروڈکٹ ہے۔ ایک مہینے میں میرے بال خشک سے چمکدار اور مضبوط ہو گئے۔ 100٪ قدرتی، فرق محسوس ہوتا ہے۔"',
      'idx.rev.c2.name': 'Muhammad Usman',
      'idx.rev.c2.city': 'کراچی',
      'idx.rev.c3.q': '"ہربل شیمپو بہت نرم ہے — نہ خشکی، نہ الجھاؤ۔ 3 ہفتوں میں خشکی مکمل ختم ہو گئی۔ بہت سفارش کرتی ہوں!"',
      'idx.rev.c3.name': 'Sara Mahmood',
      'idx.rev.c3.city': 'اسلام آباد',
      'idx.rev.c4.q': '"پہلے یقین نہیں تھا لیکن 45 دن بعد نتائج خود بول رہے ہیں۔ میری کھوپڑی صحت مند اور بالوں کا گرنا مکمل بند ہو گیا۔"',
      'idx.rev.c4.name': 'Fatima Malik',
      'idx.rev.c4.city': 'فیصل آباد',
      'idx.rev.c5.q': '"بلیک روز آئل میرا پسندیدہ ہے۔ میرے بالوں کا رنگ بہتر ہوا اور وہ بہت گھنے لگتے ہیں۔ ناصر آئل ایکسپرٹ سے آرڈر کریں — خالص معیار!"',
      'idx.rev.c5.name': 'Zara Siddiqui',
      'idx.rev.c5.city': 'راولپنڈی',
      'idx.rev.c6.q': '"دو مہینوں سے 3-in-1 کٹ استعمال کر رہا ہوں۔ بالوں کے لیے بہترین سرمایہ کاری۔ دوست پوچھتے رہتے ہیں کیا لگاتا ہوں — سب ناصر آئل ایکسپرٹ ہے!"',
      'idx.rev.c6.name': 'Hamza Raza',
      'idx.rev.c6.city': 'ملتان',

      /* Golden Oil page reviews */
      'go.rev.c1.name': 'سارہ احسن',
      'go.rev.c1.loc': 'لاہور، پاکستان',
      'go.rev.c1.q': '"گولڈن آئل نے میرے بالوں کو بالکل بدل دیا! صرف ۴ ہفتوں میں بال کتنے گھنے اور چمکدار ہو گئے۔ پہلے کنگھی کرتے وقت بہت بال گرتے تھے لیکن اب ۷۰٪ کم ہو گئے ہیں۔ میں کبھی کیمیکل پروڈکٹس کی طرف واپس نہیں جاؤں گی۔"',
      'go.rev.c2.name': 'نادیہ خان',
      'go.rev.c2.loc': 'کراچی، پاکستان',
      'go.rev.c2.q': '"میں نے بہت سے تیل آزمائے لیکن کوئی اس طرح کام نہیں کیا۔ میرے بال بہت خشک تھے اور سروں سے ٹوٹ رہے تھے۔ صرف ۳ ہفتوں کے باقاعدہ استعمال کے بعد بالکل مختلف محسوس ہوتا ہے۔ خوشبو بھی بہت اچھی اور قدرتی ہے۔"',
      'go.rev.c3.name': 'عائشہ مرزا',
      'go.rev.c3.loc': 'اسلام آباد، پاکستان',
      'go.rev.c3.q': '"میری امی اسے خشک کھوپڑی کے لیے استعمال کر رہی ہیں اور تعریف کرتے نہیں تھکتیں۔ کہتی ہیں پرانے زمانے کے خالص تیلوں جیسا ہے۔ اس بار ۳ بوتلیں منگوائیں تاکہ کم نہ پڑے!"',
      'go.rev.c4.name': 'زارا بیگ',
      'go.rev.c4.loc': 'فیصل آباد، پاکستان',
      'go.rev.c4.q': '"تیز ڈیلیوری اور بالکل ویسا ہی جیسا بتایا گیا تھا۔ پیکیجنگ صاف ستھری اور تیل بہت پریمیم محسوس ہوتا ہے۔ بالوں کو دھونے سے ایک رات پہلے لگاتی ہوں اور بال کبھی اتنے نرم نہیں تھے۔ سب کو سفارش کرتی ہوں۔"',

      /* Black Rose Oil page reviews */
      'br.rev.c1.name': 'فاطمہ ملک',
      'br.rev.c1.loc': 'کراچی، پاکستان',
      'br.rev.c1.q': '"بلیک روز آئل کی خوشبو لاجواب ہے اور کام اس سے بھی بہتر کرتا ہے۔ صرف ۳ ہفتوں میں بال گرنا بہت کم ہو گیا۔ اب سب پوچھتے ہیں کہ بالوں کو کیا لگاتی ہوں۔ بے حد پسند آیا!"',
      'br.rev.c2.name': 'رابعہ علی',
      'br.rev.c2.loc': 'ملتان، پاکستان',
      'br.rev.c2.q': '"پہلے شک تھا کیونکہ بہت سے تیل آزمائے لیکن یہ مختلف ہے۔ خوشبو بہت ہلکی اور قدرتی ہے۔ استعمال کے بعد بال بہت ملائم اور مضبوط محسوس ہوتے ہیں۔ دوبارہ ضرور آرڈر کروں گی!"',
      'br.rev.c3.name': 'ثنا حامد',
      'br.rev.c3.loc': 'لاہور، پاکستان',
      'br.rev.c3.q': '"پیکیجنگ خوبصورت ہے اور تیل بھی شاندار ہے۔ میرے رنگے ہوئے بال ہیں اور ڈر تھا کہ کوئی ردعمل نہ ہو لیکن بال اور بھی خوبصورت ہو گئے۔ جو چمک آئی ہے وہ قابل یقین نہیں۔ ۵ ستارے!"',
      'br.rev.c4.name': 'میرا ناصر',
      'br.rev.c4.loc': 'راولپنڈی، پاکستان',
      'br.rev.c4.q': '"میں نے یہ اپنی بہن کو تحفہ دیا اور انہیں اتنا پسند آیا کہ ۳ اور بوتلیں منگوا لیں۔ گلاب کی خوشبو اصلی ہے اور بالکل تیز نہیں۔ اب یہ ہمارے گھر کا ضروری حصہ بن گیا ہے۔"',

      /* Herbal Shampoo page reviews */
      'sh.rev.c1.q': '"آخرکار ایک شیمپو ملا جو بالوں کو خشک نہیں کرتا! کھوپڑی بہت صاف اور بال ناقابل یقین حد تک نرم ہیں۔ جڑی بوٹیوں کی خوشبو بہت تازگی بخش ہے۔ گولڈن آئل کے ساتھ ملا کر استعمال کر رہی ہوں اور بال کبھی اتنے اچھے نہیں لگے۔"',
      'sh.rev.c2.q': '"سالوں کی خشکی کا مسئلہ آخرکار ختم ہو گیا! صرف دو ہفتوں میں کھوپڑی صاف اور خارش سے پاک ہو گئی۔ خوب جھاگ آتا ہے اور صفائی بھی اچھی۔ ہر پیسے کے لائق ہے۔"',
      'sh.rev.c3.q': '"برانڈڈ شیمپو چھوڑنے پر شک تھا لیکن یہ جڑی بوٹیوں والا کہیں بہتر ہے! بال تیزی سے بڑھ رہے ہیں، مضبوط ہیں اور رنگ مزید نکھرا ہوا لگتا ہے۔ بالوں کی پرواہ کرنے والے ہر شخص کے لیے ضروری ہے۔"',
      'sh.rev.c4.q': '"بہترین قدرتی شیمپو اور پیاری جڑی بوٹیوں کی خوشبو۔ دھونے کے بعد بال بالکل خشک نہیں ہوتے۔ بلیک روز آئل کے ساتھ جوڑی کمال کی ہے۔ ڈیلیوری بھی تیز تھی — ۲ دنوں میں آ گیا!"',

      /* Index reels section */
      'idx.reel.label': 'حقیقی نتائج',
      'idx.reel.h2': 'گاہکوں کی کہانیاں دیکھیں',
      'idx.reel.p': 'دیکھیں ہمارے گاہک سوشل میڈیا پر کیا کہہ رہے ہیں۔',
      'idx.reel.c1': '"صرف 45 دنوں میں حیرت انگیز نتائج!"',
      'idx.reel.c2': '"بالوں کا گرنا مکمل بند ہو گیا!"',
      'idx.reel.c3': '"میرے سفید بال واپس کالے ہو گئے!"',
      'idx.reel.c4': '"پاکستان میں بہترین ہربل آئل!"',

      /* Index review links */
      'rev.viewAll': 'تمام تبصرے اور ویڈیوز دیکھیں',
      'rev.seeAll': 'تمام تبصرے دیکھیں',
      'prod.viewDetails': 'تفصیل دیکھیں',

      /* Products page */
      'pg.h1': 'پریمیم بالوں کی دیکھ بھال',
      'pg.filter.kit': '3 میں 1 کٹ',
      'pg.filter.oils': 'بالوں کے تیل',
      'pg.filter.shampoos': 'شیمپو',
      'pg.why.title': 'ناصر آئل ایکسپرٹ کا فرق',
      'pg.why.best.desc': 'سستی قیمتوں پر پریمیم معیار۔ قدرتی اجزاء، ثابت نتائج۔',
      'pg.cta.label': 'آرڈر کے لیے تیار؟',
      'pg.cta.title': 'آج اپنی بالوں کی دیکھ بھال شروع کریں',
      'pg.cta.sub': 'مصنوعات کارٹ میں شامل کریں اور چیک آؤٹ کریں، یا براہ راست واٹس ایپ پر رابطہ کریں۔',
      'pg.cta.cart': 'کارٹ دیکھیں →',
      'pg.cta.wa': 'واٹس ایپ آرڈر',
      'pg.bun.b1': '45 دن کا مکمل علاج',
      'pg.bun.b2': 'بال گرنے اور خشکی سے لڑتا ہے',
      'pg.bun.b3': 'قدرتی طور پر سفیدی کم کرتا ہے',
      'pg.bun.b4': 'ہوم ڈیلیوری دستیاب',
      'pg.bun.b5': '100% قدرتی اجزاء',

      /* About page */
      'ab.label': 'ہماری کہانی',
      'ab.h1': 'ناصر آئل ایکسپرٹ کے بارے میں',
      'ab.who.label': 'ہم کون ہیں',
      'ab.who.title': 'فطرت میں جڑے، عمدگی کے لیے پرعزم',
      'ab.stat1': 'خوش گاہک',
      'ab.stat2': 'مصنوعات کی رینج',
      'ab.stat3': 'قدرتی اجزاء',
      'ab.stat4': 'اوسط ریٹنگ',
      'ab.val1.title': 'خلوص',
      'ab.val2.title': 'اعتماد',
      'ab.val3.title': 'پائیداری',
      'ab.val4.title': 'جدت',
      'ab.val5.title': 'دیکھ بھال',
      'ab.val6.title': 'عمدگی',
      'ab.tl1.title': 'آغاز',
      'ab.tl2.title': 'پہلی مصنوعات',
      'ab.tl3.title': 'رینج کی توسیع',
      'ab.tl4.title': '5000+ خوش گاہک',
      'ab.cta.label': 'شروع کریں',
      'ab.cta.title': 'فرق محسوس کرنے کے لیے تیار؟',
      'ab.cta.shop': 'ابھی خریدیں →',
      'ab.cta.contact': 'ہم سے رابطہ کریں',

      /* Reviews page */
      'rv.verified.label': 'تصدیق شدہ تبصرے',
      'rv.h1': 'گاہکوں کے تبصرے اور کہانیاں',
      'rv.based': '200+ تبصروں پر مبنی',
      'rv.saying.title': 'گاہک کیا کہہ رہے ہیں',
      'rv.video.label': 'ویڈیو تبصرے',
      'rv.video.title': 'کسٹمر ریلز',
      'rv.video.sub': 'ہمارے گاہکوں کو اصل نتائج شیئر کرتے دیکھیں۔',
      'rv.cta.title': 'نتائج دیکھنے کے لیے تیار؟',
      'rv.cta.sub': 'پاکستان بھر میں ہزاروں مطمئن گاہکوں میں شامل ہوں۔',
      'rv.cta.shop': 'ابھی خریدیں',
      'rv.cta.wa': 'واٹس ایپ پر آرڈر کریں',
      'rv.c1.q': '"صرف 45 دن میں مکمل کٹ استعمال کرنے کے بعد، میرے بالوں کا گرنا 80% کم ہو گیا۔ بلیک روز آئل رات بھر کام کرتا ہے۔ صبح اٹھ کر بالوں کو نرم اور گھنا پاتی ہوں۔"',
      'rv.c2.q': '"ناصر گولڈن آئل واقعی کمال ہے۔ میرے بال ایک مہینے میں خشک اور کمزور سے چمکدار اور مضبوط ہو گئے۔ 100% قدرتی، فرق خود محسوس ہوتا ہے۔"',
      'rv.c3.q': '"ہربل شیمپو بہت نرم ہے، نہ خشکی، نہ الجھاؤ۔ گولڈن آئل کے ساتھ 3 ہفتے استعمال کرنے کے بعد میری خشکی بالکل ختم ہو گئی۔ بہت سفارش کرتی ہوں!"',
      'rv.c4.q': '"حمل کے بعد میرے بال تیزی سے گر رہے تھے۔ ایک دوست نے بلیک روز آئل تجویز کیا اور 6 ہفتوں میں میرے بال پہلے سے زیادہ گھنے ہو گئے۔ کمال پروڈکٹ!"',
      'rv.c5.q': '"3-اِن-1 بنڈل منگوایا اور 45 دن کا پلان بالکل فالو کیا۔ سفید بال کم ہو رہے ہیں، خشکی ختم ہو گئی، اور بال تیز اُگ رہے ہیں۔ ہر روپیہ وصول ہوا!"',
      'rv.c6.q': '"میں نے جتنے بھی تیل استعمال کیے ہیں، یہ سب سے بہترین ہے۔ کوئی کیمیکل نہیں، کوئی سائیڈ ایفیکٹ نہیں۔ کھوپڑی صحت مند لگتی ہے اور بال سارا دن تازہ خوشبودار رہتے ہیں۔"',
      'rv.c1.name': 'عائشہ خان',
      'rv.c2.name': 'محمد عثمان',
      'rv.c3.name': 'سارہ محمود',
      'rv.c4.name': 'فاطمہ صدیقی',
      'rv.c5.name': 'حسن علی',
      'rv.c6.name': 'زینب رضا',
      'rv.c1.loc': 'لاہور · تصدیق شدہ خریداری',
      'rv.c2.loc': 'کراچی · تصدیق شدہ خریداری',
      'rv.c3.loc': 'اسلام آباد · تصدیق شدہ خریداری',
      'rv.c4.loc': 'راولپنڈی · تصدیق شدہ خریداری',
      'rv.c5.loc': 'فیصل آباد · تصدیق شدہ خریداری',
      'rv.c6.loc': 'ملتان · تصدیق شدہ خریداری',
      'rv.c5.prod': '3-اِن-1 کٹ',
      'rv.vq1': '"صرف 45 دنوں میں ناقابل یقین نتائج!"',
      'rv.vq2': '"بالوں کا گرنا بالکل بند ہو گیا!"',
      'rv.vq3': '"میرے سفید بال واپس کالے ہو گئے!"',
      'rv.vq4': '"پاکستان میں بہترین جڑی بوٹی والا تیل!"',

      /* Contact page headings */
      'ct.reach.label': 'ہم سے رابطہ کریں',
      'ct.reach.title': 'رابطہ کریں',
      'ct.reach.sub': 'سوال ہے، آرڈر دینا ہے یا مدد چاہیے؟ ہم حاضر ہیں۔',
      'ct.phone.title': 'فون / کال',
      'ct.wa.h': 'واٹس ایپ آرڈر',
      'ct.email.h': 'ای میل',
      'ct.store.h': 'ہمارا اسٹور',
      'ct.social.label': 'سوشل میڈیا',
      'ct.form.title': 'ہمیں اپنا پیغام بھیجیں',
      'ct.wa.direct': 'واٹس ایپ پر براہ راست پیغام دیں',
      'ct.form.or': 'یا نیچے فارم بھریں',
      'ct.email.label': 'ای میل',
      'ct.subject.label': 'موضوع',
      'ct.maps.label': 'ہماری لوکیشن',
      'ct.maps.title': 'نقشے پر ہمیں تلاش کریں',
      'ct.maps.btn': 'گوگل میپس میں کھولیں',
      'ct.faq.label': 'عام سوالات',
      'ct.faq.title': 'اکثر پوچھے گئے سوالات',
      'ct.faq.sub': 'عام سوالات کے جوابات یہاں پائیں۔',

      /* Products page h2 */
      'pg.essentials.h2': 'اپنی بالوں کی دیکھ بھال کا انتخاب کریں',

      /* About page story + value + timeline content */
      'ab.who.p1': 'ناصر آئل ایکسپرٹ فطرت کی شفا بخش طاقت پر گہرے یقین کے ساتھ قائم ہوا۔ پاکستان میں ہر کسی کے لیے اصل، خالص تیل قابل رسائی بنانے کے مشن کے ساتھ، ہم نے سالوں محنت سے اپنی فارمولیشن کو نکھارا ہے۔',
      'ab.who.p2': 'ہماری ہر مصنوع محبت کا کام ہے — احتیاط سے حاصل کردہ، کولڈ پریسڈ، اور مصنوعی کیمیکل کے بغیر ملائی گئی۔ ہمارا یقین ہے کہ آپ کے بالوں کو فطرت کی بہترین چیز ملنی چاہیے۔',
      'ab.val1.desc': 'ہم صرف بہترین قدرتی اجزاء استعمال کرتے ہیں — کوئی مصنوعی اضافہ یا سخت کیمیکل نہیں۔ لیبل پر جو لکھا ہے وہی بوتل میں ہے۔',
      'ab.val2.desc': 'ہم شفافیت، ایمانداری اور مستقل معیار کے ذریعے گاہکوں کے ساتھ دیرپا تعلقات بناتے ہیں۔',
      'ab.val3.desc': 'ہم ذمہ داری سے نباتات حاصل کرتے ہیں اور ماحول دوست طریقوں کے لیے پرعزم ہیں۔',
      'ab.val4.desc': 'ہم مسلسل تحقیق اور بہتری کرتے ہیں، قدیم جڑی بوٹیوں کی حکمت کو جدید علم کے ساتھ جوڑتے ہیں۔',
      'ab.val5.desc': 'ہر مصنوع گاہکوں کی فلاح کے لیے سچی توجہ سے بنائی جاتی ہے۔ ہم آپ کے بالوں کی اسی توجہ سے دیکھ بھال کرتے ہیں جو ہم اپنے لیے چاہتے ہیں۔',
      'ab.val6.desc': 'ہم معیار پر کبھی سمجھوتہ نہیں کرتے۔ ہر بیچ سخت معیاری جانچ سے گزرتا ہے تاکہ آپ کو اصل نتائج ملیں۔',
      'ab.tl1.desc': 'ناصر آئل ایکسپرٹ ایک مشن کے ساتھ قائم ہوا: خالص اور اصل تیل فراہم کرنا۔ روایتی نسخوں کے ساتھ سادہ آغاز۔',
      'ab.tl2.desc': 'ہمارا خاص گولڈن آئل لانچ کیا — وہ مصنوع جس نے سب شروع کیا۔ زبردست ردعمل ملا اور جلد ہی گاہکوں کا پسندیدہ بن گیا۔',
      'ab.tl3.desc': 'بلیک روز آئل اور ہربل شیمپو شامل کیے، مکمل قدرتی بالوں کی دیکھ بھال کا نظام مکمل ہوا۔',
      'ab.tl4.desc': 'آج ہم پاکستان بھر میں ہزاروں گاہکوں کی خدمت کرتے ہیں، اور معیار اور اصلیت کے لیے ہمارا عزم اتنا ہی مضبوط ہے۔',
      'ab.cta.p': 'ان ہزاروں خوش گاہکوں میں شامل ہوں جنہوں نے ناصر آئل ایکسپرٹ سے اپنے بالوں کو بدلا ہے۔',

      /* Contact intro paragraph */
      'ct.reach.p': 'کوئی سوال ہے، آرڈر دینا ہے یا مدد چاہیے؟ ہم حاضر ہیں۔ واٹس ایپ یا فون کریں، ہم جلد جواب دیں گے۔',

      /* Contact page hero + breadcrumb */
      'ct.hero.label': 'رابطہ کریں',
      'ct.hero.h1': 'ناصر آئل ایکسپرٹ سے رابطہ کریں',
      'bc.contact': 'رابطہ',

      /* Contact form labels */
      'ct.form.name.label': 'نام',
      'ct.form.phone.label': 'فون',
      'ct.form.msg.label': 'پیغام',

      /* Contact form placeholders */
      'ct.form.name.ph': 'آپ کا نام',
      'ct.form.phone.ph': '03XX-XXXXXXX',
      'ct.form.email.ph': 'ای میل ایڈریس',
      'ct.form.msg.ph': 'اپنا پیغام یہاں لکھیں...',

      /* Contact form select options */
      'ct.subject.default': 'منتخب کریں...',
      'ct.subject.order': 'آرڈر دیں',
      'ct.subject.delivery': 'ڈیلیوری کی معلومات',
      'ct.subject.product': 'پروڈکٹ کی معلومات',
      'ct.subject.return': 'واپسی / ریفنڈ',
      'ct.subject.other': 'کچھ اور',

      /* FAQ questions & answers */
      'ct.faq.q1': 'ڈیلیوری میں کتنا وقت لگتا ہے؟',
      'ct.faq.a1': 'پاکستان کے زیادہ تر شہروں میں ڈیلیوری 2 سے 4 کام کے دن میں ہوتی ہے۔ دور دراز علاقوں میں 5 سے 7 دن لگ سکتے ہیں۔',
      'ct.faq.q2': 'کیش آن ڈیلیوری (COD) دستیاب ہے؟',
      'ct.faq.a2': 'جی ہاں! ہم پاکستان بھر میں کیش آن ڈیلیوری دیتے ہیں۔ پارسل آنے پر گھر پر ادائیگی کریں — پیشگی رقم کی ضرورت نہیں۔',
      'ct.faq.q3': 'ڈیلیوری چارجز کتنے ہیں؟',
      'ct.faq.a3': 'Rs 1500 سے زیادہ کے آرڈر پر ڈیلیوری بالکل مفت ہے۔ Rs 1500 سے کم کے آرڈر پر Rs 150 ڈیلیوری چارج لاگو ہوتا ہے۔',
      'ct.faq.q4': 'کیا مصنوعات 100% قدرتی ہیں؟',
      'ct.faq.a4': 'بالکل! ہماری تمام مصنوعات خالصتاً قدرتی نباتاتی اجزاء سے بنی ہیں۔ کوئی مصنوعی کیمیکل یا مصنوعی خوشبو نہیں۔',
      'ct.faq.q5': 'آرڈر منسوخ یا واپس کیسے کریں؟',
      'ct.faq.a5': 'آرڈر منسوخ یا واپس کرنے کے لیے بس واٹس ایپ یا فون کریں۔ ہم پوری مدد کریں گے اور عمل کو ممکنہ حد تک آسان بنائیں گے۔',
      'ct.faq.q6': 'کیا بلک آرڈر پر چھوٹ ملتی ہے؟',
      'ct.faq.a6': 'جی ہاں! 5 یا اس سے زیادہ بوتلوں کے آرڈر پر خصوصی چھوٹ دستیاب ہے۔ مزید تفصیل کے لیے ہم سے رابطہ کریں اور ہم آپ کے لیے بہترین ڈیل کا اہتمام کریں گے۔',

      /* Products page bullet items */
      'pg.golden.b1': 'بالوں کی نشوونما تیز کرتا ہے',
      'pg.golden.b2': 'بال گرنا اور ٹوٹنا کم کرتا ہے',
      'pg.golden.b3': 'قدرتی چمک بڑھاتا ہے',
      'pg.golden.b4': 'خشک کھوپڑی کو نمی دیتا ہے',
      'pg.golden.b5': '100% قدرتی، کوئی پیرابین نہیں',
      'pg.rose.b1': 'بالوں کی جڑیں مضبوط کرتا ہے',
      'pg.rose.b2': 'الجھے بالوں کو ٹھیک کرتا ہے',
      'pg.rose.b3': 'دیرپا قدرتی خوشبو',
      'pg.rose.b4': 'اینٹی آکسیڈنٹ تحفظ',
      'pg.rose.b5': 'بالوں کو ریشمی بناتا ہے',
      'pg.shampoo.b1': 'سلفیٹ اور پیرابین فری',
      'pg.shampoo.b2': 'خارش والی کھوپڑی کو سکون دیتا ہے',
      'pg.shampoo.b3': 'قدرتی طور پر خشکی کنٹرول کرتا ہے',
      'pg.shampoo.b4': 'تمام قسم کے بالوں کے لیے محفوظ',
      'pg.shampoo.b5': 'تازہ جڑی بوٹی خوشبو',

      /* Bundle Kit product detail page */
      'bk.save.badge': 'آپ کی بچت Rs 750 — بہترین بنڈل ڈیل',
      'bk.desc': 'آپ کے بالوں کی ہر ضرورت ایک کٹ میں۔ 3-in-1 مکمل ہیئر کٹ — گولڈن آئل، بلیک روز آئل اور ہربل شیمپو — 45 دن کا مکمل علاج جو بال گرنا، سفیدی، خشکی ختم کر کے قدرتی چمک واپس لاتا ہے۔',
      'bk.kit.golden.desc': 'گہری نمی اور بالوں کی نشوونما',
      'bk.kit.rose.desc': 'مضبوطی، چمک اور ٹوٹنے سے حفاظت',
      'bk.kit.shampoo.desc': 'سلفیٹ فری قدرتی کلینزر',
      'bk.trust.delivery': 'Rs 250 ڈیلیوری • Rs 5,000+ پر مفت',
      'bk.trust.cod': 'کیش آن ڈیلیوری',
      'bk.inside.p1': '3-in-1 مکمل ہیئر کٹ میں تینوں اصل مصنوعات شامل ہیں، جو مل کر ناصر آئل ایکسپرٹ کا 45 دن کا مکمل علاج فراہم کرتی ہیں۔',
      'bk.inside.p2': '<strong>گولڈن ہیئر آئل</strong> — سرسوں، ناریل، تِل اور بادام کے کولڈ پریسڈ مرکب میں جڑی بوٹی کے عرق سے بھرپور۔ جڑ سے نوک تک گہری نمی، چمک اور تیز نشوونما۔',
      'bk.inside.p3': '<strong>بلیک روز آئل</strong> — نادر بلیک روز عرق اور قدرتی تیلوں کا امتزاج۔ بالوں کو جڑ سے مضبوط کرتا ہے، نقصان سے بچاتا ہے، اور دیرپا خوشبو دیتا ہے۔',
      'bk.inside.p4': '<strong>ہربل شیمپو</strong> — نیم، آملہ، بھرنگ راج اور قدرتی عرق سے بنا۔ سلفیٹ فری اور نرم — قدرتی تیل برقرار رکھتے ہوئے صفائی، بال ملائم اور قابل انتظام۔',
      'bk.ben.1': '70% تک بال گرنا کم ہو جاتا ہے',
      'bk.ben.2': 'قدرتی طور پر قبل از وقت سفیدی روکتا ہے',
      'bk.ben.3': 'جڑوں سے خشکی ختم کرتا ہے',
      'bk.ben.4': 'بالوں کی جڑیں گہرائی سے مضبوط کرتا ہے',
      'bk.ben.5': 'قدرتی چمک نمایاں طور پر بڑھاتا ہے',
      'bk.ben.6': 'خشک اور خراب بالوں کی مرمت کرتا ہے',
      'bk.ben.7': 'نئے بالوں کی تیز نشوونما کرتا ہے',
      'bk.ben.8': 'خشک اور خارش والی کھوپڑی کو سکون دیتا ہے',
      'bk.ben.9': 'الجھے بالوں کا مسئلہ حل کرتا ہے',
      'bk.ben.10': 'دیرپا قدرتی خوشبو',
      'bk.howto.plan': 'مکمل 45 دن کا علاج منصوبہ',
      'bk.howto.p1': 'رات کو بالوں کی جڑوں پر بلیک روز آئل لگائیں، 10 راتیں مسلسل۔ آہستہ مساج کریں۔ ہر صبح ناصر ہربل شیمپو سے بال دھوئیں۔',
      'bk.howto.p2': 'بالوں کی جڑوں پر گولڈن آئل لگا کر آہستہ مساج کریں۔ رات بھر لگا رہنے دیں اور اگلی صبح ناصر ہربل شیمپو سے دھوئیں۔',
      'bk.howto.p3': 'بلیک روز آئل اور گولڈن آئل روزانہ باری باری لگاتے رہیں۔ ہر بار ناصر ہربل شیمپو سے بال صاف کریں۔',
      'bk.howto.note': '<strong>نوٹ:</strong> 45 دن بعد، بلیک روز اور گولڈن آئل 5 دن کے وقفے سے باری باری استعمال کریں۔ مکمل نتائج کے لیے 40–45 دن جاری رکھیں۔ روزانہ باقاعدہ استعمال سے بہترین نتائج ملتے ہیں۔',
      'bk.why.p1': 'انفرادی مصنوعات کی بجائے کٹ خریدنے پر فوری Rs 750 کی بچت ہوتی ہے — لیکن اصل فائدہ یہ ہے کہ پہلے دن سے مکمل علاج کا نظام آپ کے پاس ہوتا ہے، کچھ الگ آرڈر کرنے کی ضرورت نہیں۔',
      'bk.why.p2': 'ہمارے 45 دن کے علاج کے لیے تینوں مصنوعات مل کر کام کرتی ہیں۔ بطور نظام استعمال کریں تو ایک دوسرے کی تکمیل کرتی ہیں — تیل کھوپڑی کو نمی دیتے ہیں، شیمپو صفائی کرتا ہے اور نمی برقرار رہتی ہے۔',
      'bk.why.p3': 'جو گاہک پوری کٹ سے شروع کرتے ہیں وہ صرف ایک مصنوع سے بہت بہتر نتائج دیکھتے ہیں، کیونکہ علاج بال گرنا، سفیدی اور خشکی تینوں کو ایک ساتھ ختم کرتا ہے۔',
      'bk.why.saved': 'علیحدہ خریداری کی بجائے بچت',
      'bk.why.complete': 'مکمل علاج کا پروگرام',
      'bk.why.system': 'ایک مکمل بالوں کی دیکھ بھال کا نظام',
      'bk.rev.c1.name': 'حنا فاروق',
      'bk.rev.c1.loc': 'لاہور، پاکستان',
      'bk.rev.c1.q': '"پہلے یقین نہیں تھا لیکن 45 دن کے بعد نتائج ناقابل یقین ہیں۔ بال گرنا بہت کم ہوا اور بال موٹے دکھ رہے ہیں۔ شیمپو بھی کمال ہے — دھونے کے بعد خشکی نہیں۔ دوبارہ آرڈر کریں گے۔"',
      'bk.rev.c2.name': 'رباب علی',
      'bk.rev.c2.loc': 'کراچی، پاکستان',
      'bk.rev.c2.q': '"کٹ بالکل قابل خریداری ہے۔ Rs 750 کی بچت بونس ہے لیکن پوری قیمت پر بھی خریدتی۔ تینوں مصنوعات مل کر بہترین کام کرتی ہیں۔ سفید بال کم ہوئے اور بال پہلے سے تیز بڑھ رہے ہیں۔"',
      'bk.rev.c3.name': 'ثنا ملک',
      'bk.rev.c3.loc': 'اسلام آباد، پاکستان',
      'bk.rev.c3.q': '"اپنی والدہ کے لیے خریدی جن کے بال بہت گر رہے تھے۔ 45 دن کے بعد بہت فرق پڑا۔ بال بہت مضبوط ہوئے اور شیمپو سے خشکی ختم۔ بلیک روز آئل کی خوشبو بھی بہت اچھی ہے۔"',
      'bk.rev.c4.name': 'تحریم خان',
      'bk.rev.c4.loc': 'فیصل آباد، پاکستان',
      'bk.rev.c4.q': '"تیز ڈیلیوری، اچھی پیکیجنگ۔ ہدایات پر عمل کیا اور 15ویں دن فرق محسوس ہوا۔ نرم، چمکدار اور کم ٹوٹنا۔ گولڈن آئل پسندیدہ — قدرتی خوشبو اور جلدی جذب۔ کٹ کی بھرپور سفارش کرتے ہیں!"',

      /* Products page extra */
      'pg.why.label': 'ہمیں کیوں چنیں',
      'pg.why.value.title': 'بہترین قیمت',
      'pg.why.value.desc': 'سستی قیمتوں پر پریمیم معیار جو ہر بجٹ کے لیے موزوں ہو۔',
      'pg.bundles.label': 'خصوصی ڈیلز',
      'pg.bundles.h2': 'قیمتی کمبو پیکجز',
      'pg.bundles.sub': 'ہمارے خصوصی کمبو پیکجز سے مکمل بالوں کی دیکھ بھال کا تجربہ بے مثال قیمت میں حاصل کریں۔',
      'pg.combo1.name': 'گولڈن آئل + ہربل شیمپو',
      'pg.combo1.desc': 'روزانہ کی بالوں کی دیکھ بھال کا بہترین جوڑا۔ ہربل شیمپو سے صفائی اور گولڈن آئل سے گہری نمی — بالوں کی بہترین صحت کے لیے۔',
      'pg.combo2.name': 'بلیک روز آئل + ہربل شیمپو',
      'pg.combo2.desc': 'پریمیم خوشبو اور مضبوطی کے لیے شاندار مجموعہ۔ ریشمی اور مضبوط بالوں کے لیے بہترین بالوں کی دیکھ بھال کا تجربہ۔',
      'pg.bundle.save': 'Rs 200 کی بچت',

      /* About page badge + mission quote */
      'ab.badge.since': '1948<br>سے',
      'ab.badge.years': '78+ سال کی<br>عمدگی',
      'ab.mission.quote': '"قدرتی بالوں کا <span>خالص ترین، اصل ترین</span> تیل ہر گھر تک پہنچانا — لوگوں کو فطرت کی حکمت کے ذریعے صحت مند اور خوبصورت بالوں کی طرف بڑھنے کا حوصلہ دینا۔"',

      /* Product page main descriptions */
      'pd.golden.desc': 'ہمارا خاص گولڈن ہیئر آئل سرسوں، ناریل، تل اور بادام کے بہترین قدرتی تیلوں کا کولڈ پریسڈ امتزاج ہے — جڑی بوٹیوں کے عرق سے بھرپور۔ جڑ سے سرے تک گہری غذائیت، شاندار چمک، بالوں کی نشوونما اور بال گرنا نمایاں طور پر کم کرتا ہے۔',
      'pd.rose.desc': 'نایاب کالے گلاب کے عرق سے بھرپور، یہ پریمیم تیل گلاب کی خوبصورتی اور کولڈ پریسڈ قدرتی تیلوں کی طاقت کو یکجا کرتا ہے۔ بالوں کی جڑوں کو اندر سے مضبوط، ٹوٹنا کم، ریشمی چمک اور دیرپا قدرتی خوشبو۔',
      'pd.shampoo.desc': 'ہمارا ہربل شیمپو پریمیم تیلوں کا بہترین ساتھی ہے۔ نیم، آملہ، بھرنگراج اور دیگر نباتاتی عرق سے بنا — قدرتی تیلوں کو نقصان پہنچائے بغیر نرمی سے صفائی کرتا ہے۔ مکمل سلفیٹ اور پیرابین فری — روزانہ استعمال اور ہر قسم کے بالوں کے لیے محفوظ۔',

      /* How to Use phase headings */
      'pd.phase1.h4': 'مرحلہ 1 — بلیک روز آئل (10 راتیں)',
      'pd.phase2.h4': 'مرحلہ 2 — گولڈن آئل (5 دن)',
      'pd.phase3.h4': 'مرحلہ 3 — تیل باری باری (5 دن)',

      /* Ingredients — Golden Oil */
      'pd.ing.mustard': 'سرسوں کا تیل',
      'pd.ing.coconut': 'ناریل کا تیل',
      'pd.ing.sesame': 'تل کا تیل',
      'pd.ing.almond': 'بادام کا تیل',
      'pd.ing.amla': 'آملہ عرق',
      'pd.ing.bhringraj': 'بھرنگراج',

      /* Ingredients — Black Rose Oil */
      'pd.ing.blackrose': 'کالا گلاب عرق',
      'pd.ing.argan': 'آرگن آئل',
      'pd.ing.castor': 'ارنڈی کا تیل',
      'pd.ing.rosehip': 'گلاب کولہا تیل',
      'pd.ing.vitE': 'وٹامن ای',

      /* Ingredients — Herbal Shampoo */
      'pd.ing.neem': 'نیم عرق',
      'pd.ing.amlaberry': 'آملہ (ہندی آنولہ)',
      'pd.ing.coconutmilk': 'ناریل کا دودھ',
      'pd.ing.chamomile': 'بابونہ عرق',
      'pd.ing.aloe': 'ایلو ویرا جیل',
      'pd.badge.sulfate': 'سلفیٹ فری',

      /* Cart badge */
      'tag.new': 'نیا',
    }
  };

  function loadUrduFont() {
    if (document.getElementById('noe-urdu-font')) return;
    var lnk = document.createElement('link');
    lnk.id = 'noe-urdu-font';
    lnk.rel = 'stylesheet';
    lnk.href = 'https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap';
    document.head.appendChild(lnk);
  }

  function applyLang(lang) {
    var d = dict[lang] || dict.en;

    /* text-only replacements */
    document.querySelectorAll('[data-i18n]').forEach(function (el) {
      var key = el.getAttribute('data-i18n');
      if (d[key] !== undefined) el.textContent = d[key];
    });

    /* innerHTML replacements (for elements containing child tags like <span>) */
    document.querySelectorAll('[data-i18n-html]').forEach(function (el) {
      var key = el.getAttribute('data-i18n-html');
      if (d[key] !== undefined) el.innerHTML = d[key];
    });

    /* placeholder replacements */
    document.querySelectorAll('[data-i18n-placeholder]').forEach(function (el) {
      var key = el.getAttribute('data-i18n-placeholder');
      if (d[key] !== undefined) el.placeholder = d[key];
    });

    /* RTL direction & Urdu font */
    var html = document.documentElement;
    html.setAttribute('lang', lang);
    if (lang === 'ur') {
      html.classList.add('lang-ur');
      loadUrduFont();
    } else {
      html.classList.remove('lang-ur');
    }

    /* Update toggle button highlight */
    document.querySelectorAll('.lang-toggle').forEach(function (btn) {
      btn.setAttribute('data-active', lang);
    });

    try { localStorage.setItem(LANG_KEY, lang); } catch (e) {}
  }

  /* Exposed globally so onclick="noeToggleLang()" works */
  window.noeToggleLang = function () {
    var cur = 'en';
    try { cur = localStorage.getItem(LANG_KEY) || 'en'; } catch (e) {}
    applyLang(cur === 'ur' ? 'en' : 'ur');
  };

  /* Apply saved language on every page load */
  document.addEventListener('DOMContentLoaded', function () {
    var saved = 'en';
    try { saved = localStorage.getItem(LANG_KEY) || 'en'; } catch (e) {}
    if (saved === 'ur') applyLang('ur');
  });
})();
