export const formatIQD = (amount) =>
  `IQD ${Math.round(Number(amount) || 0).toLocaleString('en-US')}`;
