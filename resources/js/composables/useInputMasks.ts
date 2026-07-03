export const onlyDigits = (value: string | null | undefined): string =>
  (value || '').replace(/\D/g, '');

export const formatPhone = (value: string | null | undefined): string => {
  const digits = onlyDigits(value).slice(0, 11);

  if (digits.length <= 2) {
    return digits;
  }

  if (digits.length <= 6) {
    return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
  }

  if (digits.length <= 10) {
    return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`;
  }

  return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`;
};

export const phoneHref = (value: string | null | undefined): string => `tel:${onlyDigits(value)}`;
