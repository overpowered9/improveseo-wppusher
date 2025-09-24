import type { NextFunction, Request, Response } from 'express';
import { ZodError } from 'zod';

export function errorHandler(err: unknown, _req: Request, res: Response, _next: NextFunction) {
  if (err instanceof ZodError) {
    const z = err as ZodError;
    res.status(400).json({ error: 'validation_error', details: z.flatten() });
    return;
  }
  const e = err as any;
  const status = e?.status || 500;
  const message = e?.message || 'Internal Server Error';
  res.status(status).json({ error: 'server_error', message });
}
