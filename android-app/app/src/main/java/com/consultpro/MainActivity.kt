package com.consultpro

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent { ConsultProApp() }
    }
}

data class Professional(val name: String, val category: String)

@Composable
fun ConsultProApp() {
    var phone by remember { mutableStateOf("") }
    var otp by remember { mutableStateOf("") }
    var loggedIn by remember { mutableStateOf(false) }
    var selectedProfessional by remember { mutableStateOf<Professional?>(null) }

    val professionals = listOf(
        Professional("Adv. Priya Sharma", "Lawyer"),
        Professional("CA Rahul Verma", "CA/Auditor"),
        Professional("Dr. Neha Kapoor", "Doctor"),
        Professional("Astrologer Dev", "Astrologer"),
        Professional("Mentor Anjali Iyer", "Educational/Career Consultant")
    )

    Surface(modifier = Modifier.fillMaxSize()) {
        if (!loggedIn) {
            Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                Text("ConsultPro OTP Login", style = MaterialTheme.typography.headlineSmall)
                OutlinedTextField(value = phone, onValueChange = { phone = it }, label = { Text("Phone") })
                OutlinedTextField(value = otp, onValueChange = { otp = it }, label = { Text("OTP") })
                Button(onClick = { loggedIn = phone.isNotBlank() && otp.isNotBlank() }) {
                    Text("Verify OTP")
                }
            }
        } else if (selectedProfessional == null) {
            LazyColumn(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                item { Text("Choose a Professional", style = MaterialTheme.typography.headlineSmall) }
                items(professionals) { pro ->
                    Card(onClick = { selectedProfessional = pro }) {
                        Column(Modifier.padding(12.dp)) {
                            Text(pro.name, style = MaterialTheme.typography.titleMedium)
                            Text(pro.category)
                        }
                    }
                }
            }
        } else {
            ConsultationRoom(selectedProfessional!!) { selectedProfessional = null }
        }
    }
}

@Composable
fun ConsultationRoom(pro: Professional, onBack: () -> Unit) {
    var chatText by remember { mutableStateOf("") }
    val messages = remember { mutableStateListOf<String>() }

    Column(Modifier.fillMaxSize().padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
        Text("Consulting with ${pro.name}", style = MaterialTheme.typography.headlineSmall)
        Button(onClick = { /* WebRTC audio session setup via signaling WS */ }) { Text("Start Audio Call") }
        LazyColumn(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(8.dp)) {
            items(messages) { msg -> Text(msg) }
        }
        OutlinedTextField(
            modifier = Modifier.fillMaxWidth(),
            value = chatText,
            onValueChange = { chatText = it },
            label = { Text("Type message") }
        )
        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            Button(onClick = {
                if (chatText.isNotBlank()) {
                    messages.add("Me: $chatText")
                    chatText = ""
                }
            }) { Text("Send") }
            OutlinedButton(onClick = onBack) { Text("Back") }
        }
    }
}
