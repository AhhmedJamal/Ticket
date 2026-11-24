import api from '@/lib/axios';
import {
  ApiResponse,
  User,
  Workspace,
  WorkspaceMember,
  Page,
  Block,
  Tag,
  NotionDatabase,
  DatabaseRow,
  DatabaseView,
  Template,
  BlockComment,
  PagePermission,
  SearchResult,
} from '@/types';

// =================== Auth Service ===================
export const authService = {
  async register(data: { name: string; email: string; password: string; password_confirmation: string }) {
    const res = await api.post<ApiResponse<{ user: User; access_token: string; token_type: string }>>('/auth/register', data);
    return res.data.data;
  },

  async login(data: { email: string; password: string }) {
    const res = await api.post<ApiResponse<{ user: User; access_token: string; token_type: string }>>('/auth/login', data);
    return res.data.data;
  },

  async getMe() {
    const res = await api.get<ApiResponse<{ user: User }>>('/auth/me');
    return res.data.data.user;
  },

  async logout() {
    const res = await api.post<ApiResponse<null>>('/auth/logout');
    return res.data;
  },

  async forgotPassword(email: string) {
    const res = await api.post<ApiResponse<null>>('/auth/forgot-password', { email });
    return res.data;
  },

  async resetPassword(data: { token: string; email: string; password: string; password_confirmation: string }) {
    const res = await api.post<ApiResponse<null>>('/auth/reset-password', data);
    return res.data;
  },
};

// =================== Workspace Service ===================
export const workspaceService = {
  async getAll(): Promise<Workspace[]> {
    const res = await api.get<ApiResponse<Workspace[]>>('/workspaces');
    return res.data.data;
  },

  async getById(id: number | string): Promise<Workspace> {
    const res = await api.get<ApiResponse<Workspace>>(`/workspaces/${id}`);
    return res.data.data;
  },

  async getOne(id: number | string): Promise<Workspace> {
    return this.getById(id);
  },

  async create(data: { name: string } | string): Promise<Workspace> {
    const payload = typeof data === 'string' ? { name: data } : data;
    const res = await api.post<ApiResponse<Workspace>>('/workspaces', payload);
    return res.data.data;
  },

  async update(id: number | string, data: { name: string } | string): Promise<Workspace> {
    const payload = typeof data === 'string' ? { name: data } : data;
    const res = await api.patch<ApiResponse<Workspace>>(`/workspaces/${id}`, payload);
    return res.data.data;
  },

  async getMembers(workspaceId: number | string): Promise<{ owner: User; members: WorkspaceMember[] }> {
    const res = await api.get<ApiResponse<{ owner: User; members: WorkspaceMember[] }>>(`/workspaces/${workspaceId}/members`);
    return res.data.data;
  },

  async inviteMember(workspaceId: number | string, data: { email: string; role: 'admin' | 'member' }) {
    const res = await api.post<ApiResponse<{ member: WorkspaceMember }>>(`/workspaces/${workspaceId}/members`, data);
    return res.data;
  },

  async updateMemberRole(workspaceId: number | string, userId: number | string, role: 'admin' | 'member') {
    const res = await api.patch<ApiResponse<null>>(`/workspaces/${workspaceId}/members/${userId}/role`, { role });
    return res.data;
  },

  async removeMember(workspaceId: number | string, userId: number | string) {
    const res = await api.delete<ApiResponse<null>>(`/workspaces/${workspaceId}/members/${userId}`);
    return res.data;
  },

  async createTag(workspaceId: number | string, data: { name: string; color: string }): Promise<Tag> {
    const res = await api.post<ApiResponse<Tag>>(`/workspaces/${workspaceId}/tags`, data);
    return res.data.data;
  },

  async deleteTag(tagId: number | string) {
    const res = await api.delete<ApiResponse<null>>(`/tags/${tagId}`);
    return res.data;
  },

  async getTemplates(workspaceId: number | string): Promise<Record<string, Template[]>> {
    const res = await api.get<ApiResponse<Record<string, Template[]>>>(`/workspaces/${workspaceId}/templates`);
    return res.data.data;
  },

  async applyTemplate(workspaceId: number | string, templateId: number | string, parentId?: number | null): Promise<Page> {
    const res = await api.post<ApiResponse<Page>>(`/workspaces/${workspaceId}/templates/${templateId}/apply`, {
      parent_id: parentId,
    });
    return res.data.data;
  },
};

// =================== Page Service ===================
export const pageService = {
  async getById(id: number | string): Promise<Page> {
    const res = await api.get<ApiResponse<Page>>(`/pages/${id}`);
    return res.data.data;
  },

  async getOne(id: number | string): Promise<Page> {
    return this.getById(id);
  },

  async create(workspaceId: number | string, data: { title?: string; icon?: string; parent_id?: number | null }): Promise<Page> {
    const res = await api.post<ApiResponse<Page>>(`/workspaces/${workspaceId}/pages`, data);
    return res.data.data;
  },

  async update(id: number | string, data: Partial<Page>): Promise<Page> {
    const res = await api.patch<ApiResponse<Page>>(`/pages/${id}`, data);
    return res.data.data;
  },

  async toggleFavorite(id: number | string): Promise<{ is_favorite: boolean }> {
    const res = await api.patch<ApiResponse<{ is_favorite: boolean }>>(`/pages/${id}/favorite`);
    return res.data.data;
  },

  async delete(id: number | string) {
    const res = await api.delete<ApiResponse<null>>(`/pages/${id}`);
    return res.data;
  },

  async exportMarkdown(id: number | string): Promise<{ markdown: string; filename: string }> {
    const res = await api.get<ApiResponse<{ markdown: string; filename: string }>>(`/pages/${id}/export/markdown`);
    return res.data.data;
  },

  async attachTag(pageId: number | string, data: { tag_id?: number; tag_name?: string; tag_color?: string }): Promise<{ tags: Tag[] }> {
    const res = await api.post<ApiResponse<{ tags: Tag[] }>>(`/pages/${pageId}/tags`, data);
    return res.data.data;
  },

  async detachTag(pageId: number | string, tagId: number | string) {
    const res = await api.delete<ApiResponse<null>>(`/pages/${pageId}/tags/${tagId}`);
    return res.data;
  },

  async getPermissions(pageId: number | string): Promise<PagePermission[]> {
    const res = await api.get<ApiResponse<{ permissions: PagePermission[]; current_user_role: string | null }>>(`/pages/${pageId}/permissions`);
    return res.data.data.permissions;
  },

  async setPermission(pageId: number | string, data: { email: string; role: 'editor' | 'commenter' | 'viewer' }): Promise<PagePermission> {
    const res = await api.post<ApiResponse<PagePermission>>(`/pages/${pageId}/permissions`, data);
    return res.data.data;
  },

  async removePermission(pageId: number | string, userId: number | string) {
    const res = await api.delete<ApiResponse<null>>(`/pages/${pageId}/permissions/${userId}`);
    return res.data;
  },
};

// =================== Block Service ===================
export const blockService = {
  async create(pageId: number | string, data: { type: string; text?: string; position?: number; content?: any; [key: string]: any }): Promise<Block> {
    const res = await api.post<ApiResponse<Block>>(`/pages/${pageId}/blocks`, data);
    return res.data.data;
  },

  async update(blockId: number | string, data: Record<string, any>): Promise<Block> {
    const res = await api.patch<ApiResponse<Block>>(`/blocks/${blockId}`, data);
    return res.data.data;
  },

  async delete(blockId: number | string) {
    const res = await api.delete<ApiResponse<null>>(`/blocks/${blockId}`);
    return res.data;
  },

  async reorder(pageId: number | string, blocks: Array<{ id: number; position: number }>) {
    const res = await api.post<ApiResponse<null>>(`/pages/${pageId}/blocks/reorder`, { blocks });
    return res.data;
  },

  async getComments(blockId: number | string): Promise<BlockComment[]> {
    const res = await api.get<ApiResponse<BlockComment[]>>(`/blocks/${blockId}/comments`);
    return res.data.data;
  },

  async addComment(blockId: number | string, data: { content: string; parent_id?: number | null }): Promise<BlockComment> {
    const res = await api.post<ApiResponse<BlockComment>>(`/blocks/${blockId}/comments`, data);
    return res.data.data;
  },

  async resolveComment(commentId: number | string): Promise<BlockComment> {
    const res = await api.patch<ApiResponse<BlockComment>>(`/comments/${commentId}/resolve`);
    return res.data.data;
  },

  async deleteComment(commentId: number | string) {
    const res = await api.delete<ApiResponse<null>>(`/comments/${commentId}`);
    return res.data;
  },
};

// =================== Block Comment Service Alias ===================
export const blockCommentService = {
  getByBlock: (blockId: number | string) => blockService.getComments(blockId),
  create: (blockId: number | string, data: { content: string; parent_id?: number | null }) => blockService.addComment(blockId, data),
  toggleResolve: (commentId: number | string) => blockService.resolveComment(commentId),
  delete: (commentId: number | string) => blockService.deleteComment(commentId),
};

// =================== Page Permission Service Alias ===================
export const pagePermissionService = {
  getByPage: (pageId: number | string) => pageService.getPermissions(pageId),
  setPermission: (pageId: number | string, data: { email: string; role: 'editor' | 'commenter' | 'viewer' }) => pageService.setPermission(pageId, data),
  removePermission: (pageId: number | string, userId: number | string) => pageService.removePermission(pageId, userId),
};

// =================== Template Service ===================
export const templateService = {
  async getAll(): Promise<Record<string, Template[]>> {
    const res = await api.get<ApiResponse<Record<string, Template[]>>>('/templates');
    return res.data.data;
  },

  async apply(workspaceId: number | string, templateId: number | string, parentId?: number | null): Promise<Page> {
    return workspaceService.applyTemplate(workspaceId, templateId, parentId);
  },
};

// =================== Database Service ===================
export const databaseService = {
  async create(pageId: number | string, data?: { name?: string }): Promise<NotionDatabase> {
    const res = await api.post<ApiResponse<NotionDatabase>>(`/pages/${pageId}/databases`, data);
    return res.data.data;
  },

  async update(databaseId: number | string, data: { name?: string; properties?: any[] }): Promise<NotionDatabase> {
    const res = await api.patch<ApiResponse<NotionDatabase>>(`/databases/${databaseId}`, data);
    return res.data.data;
  },

  async delete(databaseId: number | string) {
    const res = await api.delete<ApiResponse<null>>(`/databases/${databaseId}`);
    return res.data;
  },

  async addRow(databaseId: number | string, data: { values: Record<string, any> }): Promise<DatabaseRow> {
    const res = await api.post<ApiResponse<DatabaseRow>>(`/databases/${databaseId}/rows`, data);
    return res.data.data;
  },

  async updateRow(rowId: number | string, data: { values: Record<string, any> }): Promise<DatabaseRow> {
    const res = await api.patch<ApiResponse<DatabaseRow>>(`/database-rows/${rowId}`, data);
    return res.data.data;
  },

  async deleteRow(rowId: number | string) {
    const res = await api.delete<ApiResponse<null>>(`/database-rows/${rowId}`);
    return res.data;
  },

  async addView(databaseId: number | string, data: { name: string; type: string }): Promise<DatabaseView> {
    const res = await api.post<ApiResponse<DatabaseView>>(`/databases/${databaseId}/views`, data);
    return res.data.data;
  },

  async updateView(viewId: number | string, data: Partial<DatabaseView>): Promise<DatabaseView> {
    const res = await api.patch<ApiResponse<DatabaseView>>(`/database-views/${viewId}`, data);
    return res.data.data;
  },

  async deleteView(viewId: number | string) {
    const res = await api.delete<ApiResponse<null>>(`/database-views/${viewId}`);
    return res.data;
  },
};

// =================== Search Service ===================
export const searchService = {
  async search(query: string = ''): Promise<SearchResult[]> {
    const res = await api.get<ApiResponse<SearchResult[]>>(`/search?q=${encodeURIComponent(query)}`);
    return res.data.data;
  },
};
