export default function Loading() {
  return (
    <div className="min-h-screen">
      <div className="h-20 bg-brand-blue animate-pulse" />
      <div className="max-w-6xl mx-auto px-6 py-12">
        <div className="grid gap-8 lg:grid-cols-2 lg:items-center">
          <div className="space-y-4">
            <div className="h-12 w-3/4 bg-neutral-200 rounded animate-pulse" />
            <div className="h-6 w-full bg-neutral-200 rounded animate-pulse" />
            <div className="h-6 w-5/6 bg-neutral-200 rounded animate-pulse" />
            <div className="flex gap-3 pt-4">
              <div className="h-12 w-32 bg-brand-blue/20 rounded animate-pulse" />
              <div className="h-12 w-28 bg-neutral-200 rounded animate-pulse" />
            </div>
          </div>
          <div className="hidden lg:block">
            <div className="h-80 w-full bg-neutral-200 rounded-3xl animate-pulse" />
          </div>
        </div>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-6 mt-12">
          {[...Array(4)].map((_, i) => (
            <div key={i} className="h-20 bg-neutral-200 rounded animate-pulse" />
          ))}
        </div>
        <div className="grid md:grid-cols-3 gap-6 mt-12">
          {[...Array(3)].map((_, i) => (
            <div key={i} className="h-72 bg-neutral-200 rounded-xl animate-pulse" />
          ))}
        </div>
      </div>
    </div>
  )
}
