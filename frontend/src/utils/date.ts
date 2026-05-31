export function pad2(n: number): string {
  return String(n).padStart(2, '0')
}

export function toYmd(d: Date): string {
  return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`
}

export function isLeapYear(year: number): boolean {
  return (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0
}

/** 农历公历无关：预设月日落到当年；2-29 在非闰年降为 2-28 */
export function ymdForPresetYear(year: number, month: number, day: number): string | null {
  let d = day
  if (month === 2 && day === 29 && !isLeapYear(year)) {
    d = 28
  }
  const max = daysInMonth(year, month)
  if (d > max) {
    return null
  }
  return `${year}-${pad2(month)}-${pad2(d)}`
}

function daysInMonth(year: number, month: number): number {
  return new Date(year, month, 0).getDate()
}
