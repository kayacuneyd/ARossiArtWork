module.exports = {
  content: [
    './public/**/*.php',
    './src/Views/**/*.php'
  ],
  theme: {
    extend: {
      fontFamily: {
        display: ['"Playfair Display"', 'serif'],
        body: ['Inter', 'system-ui', 'sans-serif']
      },
      colors: {
        canvas: '#f8f6f1',
        ink: '#1f2933'
      }
    }
  },
  plugins: []
};
