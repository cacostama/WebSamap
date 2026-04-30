'use server'

import { z } from 'zod'

// TODO: Implement rate limiting - 5 attempts per 15min per IP
// Use Redis or similar: see AGENTS.md spec
// import { ratelimit } from '@/lib/ratelimit'

function sanitizeInput(input: string): string {
  return input
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .trim()
}

const contactSchema = z.object({
  nombre: z.string().min(2, 'El nombre es requerido').max(100, 'Nombre demasiado largo'),
  email: z.string().email('Email inválido'),
  telefono: z.string()
    .min(8, 'Teléfono inválido')
    .regex(/^[0-9+\s\-()]+$/, 'Teléfono debe contener solo números y símbolos válidos'),
  planInteres: z.enum(['alfa', 'beta', 'gamma', 'otro']),
  presupuesto: z.enum(['150000-280000', '280000-420000', '420000-600000', 'mas600000']),
  tipoFamilia: z.enum(['individual', 'pareja', 'familia']),
  mensaje: z.string().max(1000, 'Mensaje demasiado largo').optional(),
  privacidad: z.literal(true, { errorMap: () => ({ message: 'Debes aceptar la política de privacidad' }) }),
})

export async function submitContact(formData: FormData) {
  const rawData = {
    nombre: formData.get('nombre'),
    email: formData.get('email'),
    telefono: formData.get('telefono'),
    planInteres: formData.get('planInteres'),
    presupuesto: formData.get('presupuesto'),
    tipoFamilia: formData.get('tipoFamilia'),
    mensaje: sanitizeInput(formData.get('mensaje')?.toString() || ''),
    privacidad: formData.get('privacidad'),
  }

  const result = contactSchema.safeParse(rawData)
  
  if (!result.success) {
    return { 
      success: false, 
      errors: result.error.flatten().fieldErrors 
    }
  }

  // Form submitted successfully - TODO: save to DB when schema exists, send email notification

  return { success: true }
}