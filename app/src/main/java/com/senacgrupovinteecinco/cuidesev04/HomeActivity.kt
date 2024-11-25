package com.senacgrupovinteecinco.cuidesev04

import android.app.Activity
import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import okhttp3.*
import org.json.JSONObject
import java.io.IOException

class HomeActivity : Activity() {

    private val client = OkHttpClient()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_home)

        val username = intent.getStringExtra("usuario") ?: ""
        val usernameDisplay = findViewById<TextView>(R.id.username_display)
        val newPasswordField = findViewById<EditText>(R.id.new_password)
        val changePasswordButton = findViewById<Button>(R.id.change_password_button)

        usernameDisplay.text = "Bem-vindo, $username!"

        changePasswordButton.setOnClickListener {
            val newPassword = newPasswordField.text.toString()
            changePassword(username, newPassword)
        }
    }

    private fun changePassword(username: String, newPassword: String) {
        val url = "http://164.152.51.239/api.php"
        val requestBody = FormBody.Builder()
            .add("usuario", username)
            .add("novaSenha", newPassword)
            .build()

        val request = Request.Builder()
            .url(url)
            .put(requestBody)
            .build()

        client.newCall(request).enqueue(object : Callback {
            override fun onFailure(call: Call, e: IOException) {
                runOnUiThread {
                    Toast.makeText(this@HomeActivity, "Erro: ${e.message}", Toast.LENGTH_LONG).show()
                }
            }

            override fun onResponse(call: Call, response: Response) {
                val responseBody = response.body?.string()
                val jsonResponse = JSONObject(responseBody ?: "")
                val status = jsonResponse.getString("status")

                runOnUiThread {
                    if (status == "success") {
                        Toast.makeText(this@HomeActivity, "Senha alterada com sucesso!", Toast.LENGTH_LONG).show()
                    } else {
                        val message = jsonResponse.getString("message")
                        Toast.makeText(this@HomeActivity, message, Toast.LENGTH_LONG).show()
                    }
                }
            }
        })
    }
}