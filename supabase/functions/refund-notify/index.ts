import { serve } from "https://deno.land/std@0.224.0/http/server.ts";

const TELEGRAM_BOT_TOKEN = Deno.env.get("TELEGRAM_BOT_TOKEN");
const TELEGRAM_CHAT_ID = Deno.env.get("TELEGRAM_CHAT_ID");
const RESEND_API_KEY = Deno.env.get("RESEND_API_KEY");

const corsHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Methods": "POST, OPTIONS",
  "Access-Control-Allow-Headers": "authorization, x-client-info, apikey, content-type",
};

serve(async (req) => {
  if (req.method === "OPTIONS") {
    return new Response(null, { status: 204, headers: corsHeaders });
  }

  try {
    const { booking_id } = await req.json();

    if (!booking_id) {
      return new Response(JSON.stringify({ error: "booking_id ausente" }), {
        status: 400,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    const msgText =
      `Reserva cancelada — possível reembolso\n` +
      `Booking ID: ${booking_id}\n` +
      `Verificar Infinity Pay e realizar estorno se aplicável.`;

    // =========================
    // 1) Telegram (se configurado)
    // =========================
    let telegramOk = false;
    if (TELEGRAM_BOT_TOKEN && TELEGRAM_CHAT_ID) {
      const tgResp = await fetch(`https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          chat_id: TELEGRAM_CHAT_ID,
          text: msgText,
        }),
      });

      const tgResult = await tgResp.json();
      if (!tgResp.ok) {
        console.error("TELEGRAM ERROR:", tgResult);
      } else {
        telegramOk = true;
      }
    } else {
      console.log("Telegram secrets não configuradas (TELEGRAM_BOT_TOKEN/TELEGRAM_CHAT_ID).");
    }

    // =========================
    // 2) Email (se configurado)
    // =========================
    let emailOk = false;
    if (RESEND_API_KEY) {
      const emailHtml = `
        <h2>Reserva cancelada — Reembolso necessário</h2>
        <p><b>Booking ID:</b> ${booking_id}</p>
        <p>Verificar pagamento no Infinity Pay e realizar o estorno.</p>
      `;

      const resendResp = await fetch("https://api.resend.com/emails", {
        method: "POST",
        headers: {
          "Authorization": `Bearer ${RESEND_API_KEY}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          from: "onboarding@resend.dev",
          to: ["malluhandrh2005@gmail.com"],
          subject: "Reserva cancelada — Reembolso necessário",
          html: emailHtml,
        }),
      });

      const resendResult = await resendResp.json();
      if (!resendResp.ok) {
        console.error("RESEND ERROR:", resendResult);
      } else {
        emailOk = true;
      }
    } else {
      console.log("RESEND_API_KEY não configurada (email desativado).");
    }

    // Se nenhum canal foi configurado, retorna erro claro
    if (!telegramOk && !emailOk) {
      return new Response(JSON.stringify({
        error: "Nenhum canal configurado/funcionando (Telegram e Email falharam ou não têm secrets)."
      }), {
        status: 500,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    return new Response(JSON.stringify({ ok: true, telegramOk, emailOk }), {
      status: 200,
      headers: { ...corsHeaders, "Content-Type": "application/json" },
    });

  } catch (err) {
    console.error("ERRO refund-notify:", err);
    return new Response(JSON.stringify({ error: String(err) }), {
      status: 500,
      headers: { ...corsHeaders, "Content-Type": "application/json" },
    });
  }
});
