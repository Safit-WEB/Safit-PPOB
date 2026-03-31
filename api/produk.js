import crypto from 'crypto';

export default async function handler(req, res) {
    // Mengambil kunci dari Brankas Rahasia Vercel
    const username = process.env.DIGI_USERNAME;
    const apiKey = process.env.DIGI_APIKEY;

    // Keamanan jika brankas belum diisi
    if (!username || !apiKey) {
        return res.status(500).json({ success: false, message: "Kunci API belum dipasang di Vercel!" });
    }

    // Membuat tanda tangan digital (Syarat wajib dari Digiflazz)
    const sign = crypto.createHash('md5').update(username + apiKey + "pricelist").digest('hex');

    try {
        const response = await fetch('https://api.digiflazz.com/v1/price-list', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                cmd: "prepaid", // Ambil produk prabayar (pulsa, game, token)
                username: username,
                sign: sign
            })
        });
        
        const data = await response.json();
        
        if (data.data) {
            res.status(200).json({ success: true, total_produk: data.data.length, data: data.data });
        } else {
            res.status(400).json({ success: false, message: "Digiflazz nolak: " + JSON.stringify(data) });
        }
    } catch (error) {
        res.status(500).json({ success: false, message: "Sistem Error: " + error.message });
    }
}
