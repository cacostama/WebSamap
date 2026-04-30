import type { Metadata } from 'next'
import Image from 'next/image'
import Link from 'next/link'

import blogCoverOne from '@/assets/images/blog_articles.png'
import blogCoverTwo from '@/assets/images/blog_articles2.png'
import newsletterImage from '@/assets/images/newsletter_img.png'
import { Navbar } from '@/components/marketing/navbar'
import { Footer } from '@/components/marketing/footer'

export const metadata: Metadata = {
  title: 'Blog | SAMAP - Noticias y Consejos de Salud',
  description: 'Enterate de las últimas novedades para cuidar tu salud y la de tu familia. Artículos de prevención y bienestar.',
}

export default function BlogPage() {
  const posts = [
    {
      slug: 'chequeo-preventivo',
      titulo: 'La importancia del chequeo preventivo anual',
      fecha: '15 de abril, 2024',
      resumen: 'Detectá a tiempo posibles problemas de salud con un chequeo completo.',
      imagen: blogCoverOne,
    },
    {
      slug: 'salud-cardiovascular',
      titulo: 'Cuidá tu corazón con una vida saludable',
      fecha: '10 de abril, 2024',
      resumen: 'Consejos para mantener tu sistema cardiovascular sano.',
      imagen: blogCoverTwo,
    },
    {
      slug: 'nutricion-saludable',
      titulo: 'Nutrición balanceada: la base de tu bienestar',
      fecha: '5 de abril, 2024',
      resumen: 'Aprendé a construir una dieta equilibrada y nutritiva.',
      imagen: blogCoverOne,
    },
  ]

  return (
    <div className="min-h-screen">
      <Navbar />
      
      <section className="bg-brand-blue-5 px-6 py-16">
        <div className="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[1fr_0.9fr] lg:items-center">
          <div>
            <p className="text-sm uppercase tracking-[0.24em] text-brand-blue">Blog de salud</p>
            <h1 className="mt-4 text-4xl md:text-5xl text-brand-blue" style={{ fontFamily: 'DM Serif Display, serif' }}>
              Informacion preventiva y consejos utiles para el cuidado diario.
            </h1>
            <p className="mt-4 max-w-2xl text-lg text-neutral-600">
              Una maqueta editorial para mostrar contenido, novedades y articulos que fortalecen la confianza en la marca.
            </p>
          </div>
          <div className="overflow-hidden rounded-[2rem] bg-white p-3 shadow-[0_24px_60px_rgba(39,71,103,0.12)]">
            <Image src={blogCoverTwo} alt="Lectura sobre salud preventiva" className="h-auto w-full rounded-[1.5rem] object-cover" priority />
          </div>
        </div>
      </section>

      <section className="py-20 px-6">
        <div className="max-w-6xl mx-auto grid md:grid-cols-3 gap-8">
          {posts.map((post) => (
            <article 
              key={post.slug}
              className="overflow-hidden rounded-[1.5rem] border border-neutral-200 bg-white hover:shadow-md transition-shadow"
            >
              <Image src={post.imagen} alt={post.titulo} className="h-52 w-full object-cover" />
              <div className="p-6">
                <p className="text-sm text-neutral-400">{post.fecha}</p>
                <h3 className="mt-2 text-lg text-brand-blue-dark" style={{ fontFamily: 'DM Serif Display, serif' }}>
                  {post.titulo}
                </h3>
                <p className="mt-2 text-neutral-600">{post.resumen}</p>
                <Link
                  href="/blog"
                  className="inline-block mt-4 text-sm text-brand-blue hover:underline"
                >
                  Leer mas
                </Link>
              </div>
            </article>
          ))}
        </div>
      </section>

      <section className="px-6 pb-20">
        <div className="mx-auto grid max-w-6xl gap-8 overflow-hidden rounded-[2rem] bg-white shadow-sm ring-1 ring-neutral-200 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
          <div className="h-full min-h-[280px]">
            <Image src={newsletterImage} alt="Suscripcion a novedades de salud" className="h-full w-full object-cover" />
          </div>
          <div className="p-10 md:p-12">
            <p className="text-sm uppercase tracking-[0.24em] text-brand-blue">Novedades</p>
            <h2 className="mt-3 text-3xl text-brand-blue-dark" style={{ fontFamily: 'DM Serif Display, serif' }}>
              Un espacio para educar, orientar y mantener cercania con futuros socios.
            </h2>
            <p className="mt-4 text-neutral-600">
              Este bloque sirve como referencia visual para futuras notas, contenido preventivo y campanas de confianza.
            </p>
          </div>
        </div>
      </section>

      <Footer />
    </div>
  )
}
