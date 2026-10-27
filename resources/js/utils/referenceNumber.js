/**
 * Civil Service Document Reference ID and Acronym Generator Utility
 */

export const KNOWN_ACRONYMS = {
  'finance and accounts': 'F&A',
  'finance & accounts': 'F&A',
  'revenue and investment': 'R&I',
  'revenue & investment': 'R&I',
  'information and communications technology': 'ICT',
  'information & communications technology': 'ICT',
  'procurement': 'PROC',
  'administration': 'ADM',
  'general administration': 'GEN-ADM',
  'funds': 'FD',
  'funds admin': 'FD-ADM',
  'internal audit': 'IA',
  'special duties': 'SD',
  'consolidated accounts': 'CONACC',
  'treasury': 'TRY',
  'inspectorate': 'INSP',
  'pension': 'PEN',
  'welfare': 'WELF',
  'human resources': 'HR',
  'planning, research and statistics': 'PRS',
  'planning, research & statistics': 'PRS',
  'expenditure': 'EXP',
  'fiscal': 'FISC',
  'payroll': 'PAY',
  'taxation': 'TAX',
  'revenue': 'REV',
  'audit': 'AUD',
};

export const DOC_TYPE_ABBREVIATIONS = {
  'memo': 'MEMO',
  "memo's": 'MEMO',
  'memorandum': 'MEMO',
  'letter': 'LTR',
  'letters': 'LTR',
  'query': 'QRY',
  'award letter': 'AL',
  'demand notice': 'DN',
  'voucher': 'VOUCH',
  'vouchers': 'VOUCH',
  'mandate': 'MAND',
  'mandates': 'MAND',
  'confirmed mandates': 'CMAND',
  'online mandate': 'OMAND',
  'circular': 'CIRC',
  'dispatch': 'DISP',
  'recurrent expenditure': 'REC-EXP',
  'capital expenditure': 'CAP-EXP',
  'reports': 'REP',
  'report': 'REP',
  'minutes': 'MIN',
  'minutes of tenders board': 'MTB',
  'warrants': 'WARR',
  'cashbook': 'CB',
  'repayment': 'RPMT',
  'complete file': 'FILE',
};

export function generateAcronym(title) {
  if (!title) return 'GEN';
  const clean = title.trim();
  const normalized = clean.toLowerCase();

  if (KNOWN_ACRONYMS[normalized]) {
    return KNOWN_ACRONYMS[normalized];
  }

  if (clean.includes('&')) {
    const parts = clean.split('&');
    if (parts.length >= 2 && parts[0].trim() && parts[1].trim()) {
      return (parts[0].trim()[0] + '&' + parts[1].trim()[0]).toUpperCase();
    }
  }

  const words = clean.split(/\s+/);
  if (words.length > 1) {
    const stopWords = ['and', 'of', 'the', 'for', 'in', 'to', 'on', 'with', 'at', 'by'];
    const initials = words
      .filter(w => !stopWords.includes(w.toLowerCase()))
      .map(w => w[0])
      .join('')
      .toUpperCase();
    if (initials.length >= 2) {
      return initials;
    }
  }

  const alpha = clean.replace(/[^A-Za-z0-9]/g, '');
  return alpha.substring(0, Math.min(4, alpha.length)).toUpperCase() || 'GEN';
}

export function getDocTypeAbbr(typeName) {
  if (!typeName) return 'DOC';
  const clean = typeName.trim().toLowerCase();
  return DOC_TYPE_ABBREVIATIONS[clean] || generateAcronym(typeName);
}

export function computeReferencePrefix(deptTitle, divTitle, unitTitle, secTitle, customOverride) {
  if (customOverride && customOverride.trim()) {
    return customOverride.trim().replace(/^\/+|\/+$/g, '');
  }

  const parts = ['OAGF'];

  if (deptTitle) {
    parts.push(generateAcronym(deptTitle));

    if (divTitle) {
      parts.push('DIV-' + generateAcronym(divTitle));

      if (unitTitle) {
        parts.push('U-' + generateAcronym(unitTitle));

        if (secTitle) {
          parts.push('SEC-' + generateAcronym(secTitle));
        }
      }
    }
  } else {
    parts.push('GEN');
  }

  return parts.join('/');
}

export function previewFullReferenceId(deptTitle, divTitle, unitTitle, secTitle, docTypeName, customOverride) {
  const prefix = computeReferencePrefix(deptTitle, divTitle, unitTitle, secTitle, customOverride);
  const typeAbbr = getDocTypeAbbr(docTypeName);
  const year = new Date().getFullYear();
  return `${prefix}/${typeAbbr}/${year}/0001`;
}
