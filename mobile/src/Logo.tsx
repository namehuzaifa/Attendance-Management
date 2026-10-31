/**
 * The HRMS mark, inlined so it renders instantly and inherits no network
 * dependency. Identical artwork to the portal's logo and the launcher icon.
 */
export default function Logo({ size = 76, className }: { size?: number; className?: string }) {
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 1024 1024"
      className={className}
      role="img"
      aria-label="HRMS"
    >
      <defs>
        <linearGradient id="hrmsMark" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" stopColor="#6c5ce7" />
          <stop offset="55%" stopColor="#8b7cff" />
          <stop offset="100%" stopColor="#4fc8ff" />
        </linearGradient>
      </defs>

      <rect width="1024" height="1024" rx="232" fill="url(#hrmsMark)" />

      <circle cx="512" cy="512" r="290" fill="none" stroke="#fff" strokeOpacity="0.38" strokeWidth="56" />
      <circle
        cx="512"
        cy="512"
        r="290"
        fill="none"
        stroke="#fff"
        strokeWidth="56"
        strokeLinecap="round"
        strokeDasharray="1367 456"
        transform="rotate(-90 512 512)"
      />

      <circle cx="512" cy="442" r="78" fill="#fff" />
      <path d="M374 700a138 138 0 0 1 276 0z" fill="#fff" />
    </svg>
  )
}
