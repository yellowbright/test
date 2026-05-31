import { http } from './http'

export type FestivalCategory = 'default' | 'love' | 'western' | 'traditional' | 'memorial'

export interface FestivalPreset {
  id: number
  category: FestivalCategory
  name: string
  month: number
  day: number
  is_active: boolean
}

export async function fetchPresets(category: FestivalCategory): Promise<FestivalPreset[]> {
  const { data } = await http.get<{ data: FestivalPreset[] }>('/api/festival-presets', {
    params: { category },
  })
  return data.data
}
