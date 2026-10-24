<?php
require __DIR__ . '/config.php';

$sampleProducts = [
    [
        'name' => 'Titanium Hex Bolts M8 x 40mm (Pack of 50)',
        'sku' => 'BOLT-HEX-M8-TI',
        'price' => 48.50,
        'stock' => 42,
        'desc' => 'High-tensile Grade 5 Titanium (Ti-6Al-4V) hexagonal bolts. Precision CNC machined, anti-corrosion, suitable for aerospace, marine, and high-stress industrial assemblies.',
        'image' => 'uploads/products/product_1763471229_691c6f7de4439.png'
    ],
    [
        'name' => 'Billet Aluminum 6061-T6 Round Bar 2.5" OD',
        'sku' => 'RAW-AL-6061-25',
        'price' => 64.20,
        'stock' => 18,
        'desc' => 'Standard structural aircraft-grade 6061-T6 extruded aluminum solid round rod. Excellent machinability, weldability, and strength-to-weight ratio for custom fabrication.',
        'image' => 'uploads/products/product_1763471607_691c70f71ef68.png'
    ],
    [
        'name' => 'High-Torque Stepper Motor NEMA 23 2.8N.m',
        'sku' => 'MOT-NEMA23-28NM',
        'price' => 89.00,
        'stock' => 6,
        'desc' => 'Dual-shaft bipolar stepper motor engineered for CNC routers, milling workstations, and automated pick-and-place gantry systems. 1.8 degree step angle with shielded cable.',
        'image' => 'uploads/products/product_1763471877_691c720567ed8.png'
    ],
    [
        'name' => 'Precision Digital Caliper 150mm Stainless',
        'sku' => 'TOOL-CALIP-150SS',
        'price' => 54.90,
        'stock' => 12,
        'desc' => 'Hardened stainless steel digital measuring tool with IP54 water/dust resistance. Features zero calibration, metric/inch conversion, and smooth thumb-roller adjustment.',
        'image' => 'uploads/products/product_1763472065_691c72c116670.png'
    ],
    [
        'name' => 'Industrial Silicone O-Ring Assortment (382 Pcs)',
        'sku' => 'SEAL-KIT-SI-382',
        'price' => 32.75,
        'stock' => 4,
        'desc' => 'Comprehensive 30-size metric sealing ring kit fabricated from high-grade vulcanized silicone. Heat resistant from -60°C to +220°C for hydraulic and pneumatic repairs.',
        'image' => 'uploads/products/product_1763472196_691c734438343.png'
    ],
    [
        'name' => 'Solid Carbide 4-Flute End Mill 10mm AlTiN',
        'sku' => 'MILL-CR-4F-10MM',
        'price' => 38.00,
        'stock' => 0,
        'desc' => 'Micro-grain tungsten carbide milling cutter with AlTiN nano-coating. Designed for aggressive roughing and slotting in alloy steels, titanium, and cast iron.',
        'image' => 'uploads/products/product_1763472387_691c7403f3596.png'
    ],
    [
        'name' => 'Polyurethane Pneumatic Hose 8mm OD x 100m',
        'sku' => 'PNEU-HOSE-PU8-100',
        'price' => 115.00,
        'stock' => 8,
        'desc' => 'Kink-resistant flexible blue polyurethane pneumatic tubing reel. Rated for working pressures up to 1.0 MPa (150 PSI) across robotic and workshop air-supply circuits.',
        'image' => 'uploads/products/product_1763472480_691c74602acc5.png'
    ],
    [
        'name' => 'Thermal Grip Work Gloves Cut-Resistant ANSI A4',
        'sku' => 'PPE-GLV-THRM-A4',
        'price' => 19.50,
        'stock' => 75,
        'desc' => 'High-dexterity winterized workshop safety gloves. HPPE fiber knit liner with micro-foam nitrile palm coating for oily, abrasive, and cold parts handling.',
        'image' => 'assets/product1.jpg'
    ],
    [
        'name' => 'Linear Motion Bearing Block Carriage HGH20CA',
        'sku' => 'BER-LIN-HGH20CA',
        'price' => 27.30,
        'stock' => 0,
        'desc' => 'Heavy-load square type linear guideway slide block with integrated grease fitting and double-lip dustproof seals for industrial linear slide rails.',
        'image' => null
    ]
];

$stmt = $conn->prepare("INSERT INTO products (product_name, sku, price, stock, description, image_path) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), price=VALUES(price), stock=VALUES(stock), description=VALUES(description), image_path=VALUES(image_path)");

$inserted = 0;
foreach ($sampleProducts as $p) {
    $stmt->bind_param('ssdiss', $p['name'], $p['sku'], $p['price'], $p['stock'], $p['desc'], $p['image']);
    if ($stmt->execute()) {
        $inserted++;
    } else {
        echo "Error inserting " . $p['sku'] . ": " . $stmt->error . "\n";
    }
}
$stmt->close();
$conn->close();

echo "Successfully seeded {$inserted} inventory products!\n";

