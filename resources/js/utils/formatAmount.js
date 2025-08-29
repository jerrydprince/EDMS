export function formatAmount(amount) {
  if (amount >= 1_000_000_000_000) {
    return (amount / 1_000_000_000_000).toFixed(2).replace(/\.00$/, "") + "T";
  } else if (amount >= 1_000_000_000) {
    return (amount / 1_000_000_000).toFixed(2).replace(/\.00$/, "") + "B";
  } else if (amount >= 1_000_000) {
    return (amount / 1_000_000).toFixed(2).replace(/\.00$/, "") + "M";
  } else if (amount >= 1_000) {
    return (amount / 1_000).toFixed(2).replace(/\.00$/, "") + "K";
  } else {
    return amount;
  }
}

export function formatWithCommas(amount) {
  return new Intl.NumberFormat('en-NG').format(amount)
}
