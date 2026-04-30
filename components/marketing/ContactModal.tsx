"use client"

import { useState } from 'react'
import { useFormStatus } from 'react-dom'
import { Modal } from '@/components/ui/modal'
import { Button } from '@/components/ui/button'
import { submitContact } from '@/app/actions/contact'

interface ContactModalProps {
  open: boolean
  onClose: () => void
}

function SubmitButton() {
  const { pending } = useFormStatus()
  return (
    <Button type="submit" className="w-full" disabled={pending}>
      {pending ? 'Enviando...' : 'Enviar consulta'}
    </Button>
  )
}

export function ContactModal({ open, onClose }: ContactModalProps) {
  const [state, setState] = useState<{ success?: boolean; errors?: Record<string, string[]> }>({})

  async function handleSubmit(formData: FormData) {
    // Bot protection - honeypot
    const honeypot = formData.get('website')
    if (honeypot && honeypot.toString().length > 0) {
      // Silently reject bot submissions
      return
    }

    const result = await submitContact(formData)
    if (result.success) {
      setState({ success: true })
    } else {
      setState({ errors: result.errors })
    }
  }

  if (state.success) {
    return (
      <Modal open={open} onClose={onClose} title="¡Gracias por contactarnos!">
        <div className="text-center py-8">
          <div className="text-5xl mb-4">✓</div>
          <h3 className="text-xl text-brand-blue-dark mb-2">Tu consulta fue enviada</h3>
          <p className="text-neutral-600 mb-6">
            Nuestro equipo se contactará contigo en las próximas 24 horas.
          </p>
          <Button onClick={onClose}>Cerrar</Button>
        </div>
      </Modal>
    )
  }

  return (
    <Modal open={open} onClose={onClose} title="Cotizá tu plan">
      <form action={handleSubmit} className="space-y-4">
        <div>
          <label htmlFor="nombre" className="block text-sm font-medium text-neutral-700 mb-1">
            Nombre completo *
          </label>
          <input
            type="text"
            id="nombre"
            name="nombre"
            required
            className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-blue"
          />
          {state.errors?.nombre && <p className="text-red-500 text-sm">{state.errors.nombre[0]}</p>}
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-neutral-700 mb-1">
              Email *
            </label>
            <input
              type="email"
              id="email"
              name="email"
              required
              className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-blue"
            />
          </div>
          <div>
            <label htmlFor="telefono" className="block text-sm font-medium text-neutral-700 mb-1">
              Teléfono *
            </label>
            <input
              type="tel"
              id="telefono"
              name="telefono"
              required
              className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-blue"
            />
          </div>
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label htmlFor="planInteres" className="block text-sm font-medium text-neutral-700 mb-1">
              Plan de interés
            </label>
            <select
              id="planInteres"
              name="planInteres"
              className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-blue"
            >
              <option value="alfa">Plan Alfa</option>
              <option value="beta">Plan Beta</option>
              <option value="gamma">Plan Gamma</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          <div>
            <label htmlFor="presupuesto" className="block text-sm font-medium text-neutral-700 mb-1">
              Presupuesto mensual
            </label>
            <select
              id="presupuesto"
              name="presupuesto"
              className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-blue"
            >
              <option value="150000-280000">Gs. 150.000 - 280.000</option>
              <option value="280000-420000">Gs. 280.000 - 420.000</option>
              <option value="420000-600000">Gs. 420.000 - 600.000</option>
              <option value="mas600000">Más de Gs. 600.000</option>
            </select>
          </div>
        </div>

        <div>
          <label htmlFor="tipoFamilia" className="block text-sm font-medium text-neutral-700 mb-1">
            Tipo de familia
          </label>
          <select
            id="tipoFamilia"
            name="tipoFamilia"
            className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-blue"
          >
            <option value="individual">Individual</option>
            <option value="pareja">Pareja</option>
            <option value="familia">Familia</option>
          </select>
        </div>

        <div>
          <label htmlFor="mensaje" className="block text-sm font-medium text-neutral-700 mb-1">
            Mensaje (opcional)
          </label>
          <textarea
            id="mensaje"
            name="mensaje"
            rows={3}
            className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-blue resize-none"
          />
        </div>

        <div className="flex items-start gap-2">
          <input
            type="checkbox"
            id="privacidad"
            name="privacidad"
            required
            className="mt-1 h-4 w-4 text-brand-blue border-neutral-300 rounded focus:ring-brand-blue"
          />
          <label htmlFor="privacidad" className="text-sm text-neutral-600">
            Acepto la{' '}
            <a href="/privacidad" target="_blank" className="text-brand-blue hover:underline">
              política de privacidad
            </a>{' '}
            y autorizo el contacto comercial *
          </label>
        </div>
        {state.errors?.privacidad && <p className="text-red-500 text-sm">{state.errors.privacidad[0]}</p>}

        {/* Honeypot - bot detection */}
        <input
          type="text"
          name="website"
          tabIndex={-1}
          autoComplete="off"
          className="hidden"
          aria-hidden="true"
        />

        <SubmitButton />
      </form>
    </Modal>
  )
}