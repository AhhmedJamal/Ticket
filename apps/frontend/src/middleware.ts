import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const token = request.cookies.get('access_token')?.value;

  // Protected paths requiring authentication
  const isProtectedPath =
    pathname.startsWith('/workspaces') ||
    pathname.startsWith('/pages') ||
    pathname.startsWith('/dashboard') ||
    pathname === '/';

  // Auth pages (login, register, forgot-password)
  const isAuthPage =
    pathname.startsWith('/login') ||
    pathname.startsWith('/register') ||
    pathname.startsWith('/forgot-password') ||
    pathname.startsWith('/reset-password');

  // If user has token and visits login/register, redirect to home
  if (token && isAuthPage) {
    return NextResponse.redirect(new URL('/', request.url));
  }

  // Next.js client-side context also handles localStorage auth validation.
  return NextResponse.next();
}

export const config = {
  matcher: [
    /*
     * Match all request paths except for the ones starting with:
     * - api (API routes)
     * - _next/static (static files)
     * - _next/image (image optimization files)
     * - favicon.ico, fonts, public files
     */
    '/((?!api|_next/static|_next/image|fonts|favicon.ico).*)',
  ],
};

