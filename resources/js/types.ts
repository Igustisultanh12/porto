export type ContentType = 'project' | 'research' | 'article';
export interface Media {
    id: number;
    slot: string;
    url: string;
    name: string;
    mime: string;
}
export interface Taxonomy {
    id: number;
    kind: string;
    name: string;
}
export interface Author {
    name: string;
    affiliation: string;
}
export interface Metadata {
    role?: string;
    period?: string;
    problem?: string;
    approach?: string;
    result?: string;
    demo_url?: string;
    repository_url?: string;
    dataset_url?: string;
    code_url?: string;
    venue?: string;
    doi?: string;
    academic_status?: string;
    authors?: Author[];
    publication_date?: string;
}
export interface Content {
    id: number;
    type: ContentType;
    title: string;
    slug: string;
    summary: string;
    body: string;
    body_html: string;
    status: string;
    featured: boolean;
    sort_order: number;
    year: number | null;
    publication_type: string;
    metadata: Metadata;
    seo_title: string;
    seo_description: string;
    url: string;
    media: Media[];
    taxonomies: Taxonomy[];
    deleted_at: string | null;
}
export interface Entry {
    id: number;
    kind: string;
    title: string;
    subtitle: string;
    body: string;
    url: string;
    sort_order: number;
}
export interface Site {
    name?: string;
    headline?: string;
    bio?: string;
    location?: string;
    research_interests?: string;
    contact_email?: string;
    seo_title?: string;
    seo_description?: string;
}
export interface Shared {
    [key: string]: unknown;
    site: Site;
    siteMedia: Media[];
    socials: Entry[];
    auth: { user: { id: number; name: string; email: string; is_admin: boolean } | null };
    flash: { success: string | null };
}
export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}
export interface Message {
    id: number;
    name: string;
    email: string;
    subject: string;
    message: string;
    read_at: string | null;
    archived_at: string | null;
    created_at: string;
}
