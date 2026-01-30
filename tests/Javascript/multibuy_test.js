
// --- LOGIC UNDER TEST (Copied from cashier.js) ---
const PricingStrategies = {
    MultiBuy: (unitPrice, qty, tiers) => {
        if (!tiers || tiers.length === 0) return unitPrice * qty;
        const sortedTiers = [...tiers].sort((a, b) => b.quantity - a.quantity);
        let remainingQty = qty;
        let totalPrice = 0.0;
        for (const tier of sortedTiers) {
            if (remainingQty >= tier.quantity) {
                const numBundles = Math.floor(remainingQty / tier.quantity);
                totalPrice += numBundles * parseFloat(tier.price);
                remainingQty %= tier.quantity;
            }
        }
        if (remainingQty > 0) totalPrice += remainingQty * unitPrice;
        return totalPrice;
    }
};

// --- TEST RUNNER ---
function assert(message, actual, expected) {
    // Allow for small floating point diffs
    const passed = Math.abs(actual - expected) < 0.01;

    if (passed) {
        console.log(`PASS: ${message}`);
    } else {
        console.error(`FAIL: ${message} (Expected: ${expected}, Got: ${actual})`);
        process.exit(1);
    }
}

// --- TEST CASES ---

// 1. Standard Price (No Tiers)
assert('Standard Price (No Tiers)', PricingStrategies.MultiBuy(10, 5, []), 50);

// 2. Single Tier Exact
// 10 for 50 (5.0/unit)
let tiers1 = [{ quantity: 10, price: 50.0 }];
assert('Single Tier Exact', PricingStrategies.MultiBuy(10, 10, tiers1), 50);

// 3. Single Tier Multiple
assert('Single Tier Multiple', PricingStrategies.MultiBuy(10, 20, tiers1), 100);

// 4. Tier + Remainder
// 1 bundle (50) + 2 units (20) = 70
assert('Tier + Remainder', PricingStrategies.MultiBuy(10, 12, tiers1), 70);

// 5. Multiple Tiers Greedy
// 10 for 50
// 5 for 30
let tiers2 = [
    { quantity: 10, price: 50.0 },
    { quantity: 5, price: 30.0 }
];
// 17 = 1x10(50) + 1x5(30) + 2x1(20) = 100
assert('Multiple Tiers Greedy', PricingStrategies.MultiBuy(10, 17, tiers2), 100);

// 6. Unordered Tiers
let tiers3 = [
    { quantity: 5, price: 30.0 },
    { quantity: 10, price: 50.0 }
];
assert('Unordered Tiers', PricingStrategies.MultiBuy(10, 17, tiers3), 100);

console.log("All tests passed");
