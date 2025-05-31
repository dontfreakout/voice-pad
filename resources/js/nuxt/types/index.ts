export interface Sound {
  id: number | string;
  name: string;
  file_url: string;
  duration_seconds?: number; // Changed from 'duration' to reflect SoundItem prop
  file_path?: string; // From original SoundItem blade, might be useful
  category_id?: number;
  category?: {
    id: number;
    name: string;
  };
  description?: string; // From SoundItem template
  // Add other relevant properties from your actual API response
}

// You might also want a Category type if not already defined elsewhere
export interface Category {
  id: number | string;
  name: string;
  slug?: string;
  description?: string;
  sounds_count?: number;
  // Add other relevant properties
}
