export interface User {
  id: number;
  name: string;
  email: string;
}

export interface SeoData {
  title: string;
  description: string | null;
  image: string | null;
  canonical: string | null;
  robots: string;
  type: string;
  twitterCard: string;
}

export interface PageProps {
  appName: string;
  auth: { user: User | null };
  flash: { success: string | null; error: string | null };
  seo: SeoData;
  [key: string]: unknown;
}

export interface MediaAsset {
  id: string;
  original_name: string;
  mime_type: string;
  size: number;
  url: string;
}

export interface SelectOption {
  id?: number;
  name?: string;
  slug?: string;
  value?: string | number;
  label?: string;
  state_code?: string;
}

export interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

export interface Paginated<T> {
  data: T[];
  links: PaginationLink[];
  current_page: number;
  last_page: number;
  from: number | null;
  to: number | null;
  total: number;
}

export interface ListingImage {
  id: number;
  url: string;
  alt_text: string | null;
  is_cover: boolean;
}

export interface ListingCard {
  id: number;
  title: string;
  slug: string;
  category: string | null;
  price: string;
  price_value?: string;
  city: string;
  state: string;
  status?: string;
  status_label?: string;
  published_at: string | null;
  url?: string;
  cover_url: string | null;
  edit_url?: string;
  public_url?: string | null;
}

export interface ListingDetail extends ListingCard {
  description: string;
  category_id?: number;
  contact_name: string;
  contact_email?: string | null;
  contact_phone: string | null;
  expires_at?: string | null;
  images: ListingImage[];
  views_count?: number;
}
