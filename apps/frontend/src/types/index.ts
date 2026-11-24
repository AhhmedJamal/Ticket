export interface User {
  id: number;
  name: string;
  email: string;
  role?: 'admin' | 'member';
  created_at?: string;
}

export interface WorkspaceMember extends User {
  pivot?: {
    role: 'admin' | 'member';
  };
}

export interface Workspace {
  id: number;
  name: string;
  slug: string;
  user_id: number;
  owner?: User;
  members?: WorkspaceMember[];
  pages?: Page[];
  tags?: Tag[];
  created_at?: string;
}

export interface Page {
  id: number;
  workspace_id: number;
  parent_id?: number | null;
  title: string;
  icon?: string;
  cover_image?: string | null;
  cover_color?: string | null;
  is_favorite: boolean;
  created_by?: number;
  user_role?: 'editor' | 'commenter' | 'viewer' | null;
  creator?: User;
  parent?: Page | null;
  children?: Page[];
  blocks?: Block[];
  tags?: Tag[];
  databases?: NotionDatabase[];
  permissions?: PagePermission[];
  activities?: PageActivity[];
  created_at: string;
  updated_at: string;
}

export interface Block {
  id: number;
  page_id: number;
  type: string;
  content: {
    text?: string;
    checked?: boolean;
    open?: boolean;
    color?: string;
    language?: string;
    link_page_id?: number;
  };
  position: number;
  comments?: BlockComment[];
  created_at?: string;
}

export interface Tag {
  id: number;
  workspace_id?: number;
  name: string;
  color: 'gray' | 'red' | 'orange' | 'yellow' | 'green' | 'blue' | 'purple' | 'pink';
  created_at?: string;
}

export interface DatabaseProperty {
  name: string;
  type: 'text' | 'number' | 'checkbox' | 'date' | 'select' | 'multi_select' | 'url' | 'email';
  options?: string[];
}

export interface DatabaseRow {
  id: number;
  notion_database_id: number;
  values: Record<string, any>;
  position: number;
  created_at?: string;
}

export interface DatabaseView {
  id: number | null;
  notion_database_id?: number;
  name: string;
  type: 'table' | 'board' | 'list' | 'gallery';
  filter_config?: any;
  sort_config?: any;
  visible_properties?: string[];
  position?: number;
  created_at?: string;
}

export interface NotionDatabase {
  id: number;
  page_id: number;
  name: string;
  properties: DatabaseProperty[];
  rows?: DatabaseRow[];
  views?: DatabaseView[];
  activeView?: 'table' | 'board' | 'list' | 'gallery';
  searchQuery?: string;
  filterStatus?: string;
  created_at?: string;
}

export interface Template {
  id: number;
  name: string;
  description: string;
  icon: string;
  category: string;
  blocks_data: Array<{
    type: string;
    content: Record<string, any>;
  }>;
  is_default: boolean;
  created_at?: string;
}

export interface BlockComment {
  id: number;
  block_id: number;
  user_id: number;
  parent_id?: number | null;
  content: string;
  is_resolved: boolean;
  user?: User;
  replies?: BlockComment[];
  created_at?: string;
}

export interface PagePermission {
  id: number;
  page_id: number;
  user_id: number;
  role: 'editor' | 'commenter' | 'viewer';
  user?: User;
  created_at?: string;
}

export interface PageActivity {
  id: number;
  page_id: number;
  user_id: number;
  action: string;
  description?: string;
  user?: User;
  created_at: string;
}

export interface ApiResponse<T = any> {
  status: 'success' | 'error' | boolean;
  message?: string;
  data: T;
  meta?: Record<string, any>;
  errors?: Record<string, string[]>;
}

export interface SearchResultItem {
  type: 'action' | 'page' | 'block';
  id?: string | number;
  title: string;
  icon?: string;
  category?: string;
  action?: string;
  workspace?: string;
  workspace_id?: number;
  url?: string;
  match?: string;
  excerpt?: string;
}

export type SearchResult = SearchResultItem;
