import { createContext, useEffect, useState } from "react"
import { useForm, usePage } from "@inertiajs/react"
import Toastify from 'toastify-js'

// LAYOUT
import ExamLayout from "@/components/layouts/exam"
import { HeroExam } from "@/components/ui/hero-student"

// UI
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Checkbox } from "@/components/ui/checkbox"
import { Label } from "@/components/ui/label"
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group"
import { Textarea } from "@/components/ui/textarea"

function htmlDecode(input) {
  const doc = new DOMParser().parseFromString(input, "text/html")
  return doc.documentElement.textContent
}

export const QuestionIndexContext = createContext()

export default function StudentSchoolExam() {
  const { 
    schoolExamData, 
    questionData, 
    title, 
    flash_message: flashMessage
   } = usePage().props
  const [questionIndex, setQuestionIndex] = useState(1)
  const { data, setData, post, transform, processing, errors, reset } = useForm({
    answers: localStorage.getItem("answers") ? JSON.parse(localStorage.getItem("answers")) : Array(questionData.length).fill(undefined),
    school_exam_id: questionData[questionIndex - 1].school_exam_id,
  })
  const handleAnswer = (value, pgChoice) => {
    const newAnswers = [...data.answers]

    if (questionData[questionIndex - 1].question_type === "Pilihan Ganda Complex") {
      questionData[questionIndex - 1].choices.map((choice, index) => {
        if (typeof newAnswers[questionIndex - 1] === "undefined" || newAnswers[questionIndex - 1] == null) {
          newAnswers[questionIndex - 1] = []
        }

        if (pgChoice === choice.id) {
          if (newAnswers[questionIndex - 1][index] != null) {
            newAnswers[questionIndex - 1][index] = null
          } else {
            newAnswers[questionIndex - 1][index] = value
          }
        }
      })
    } else {
      newAnswers[questionIndex - 1] = value
    }

    setData("answers", newAnswers)
    localStorage.setItem("answers", JSON.stringify(newAnswers))
  }

  transform((data) => ({
    ...data,
    answers: data.answers,
  }))

  function createSchoolExamSubmit(e) {
    e.preventDefault()

    post("/student/exam/submit", {
      onSuccess: () => {
        Toastify({
          text: flashMessage ?? 'Selamat anda telah menyelesaikan ulangan',
          duration: 3000, 
          close: true,
          gravity: "top", 
          position: "right",
          backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
        }).showToast();

        reset()
        localStorage.removeItem('answers');
      }
    })
  }

  const endTime = new Date(schoolExamData.examSetting.end_time)
  const totalSeconds = Math.floor((endTime - new Date()) / 1000)
  const time = new Date()
  time.setSeconds(totalSeconds)

  return (
    <QuestionIndexContext.Provider value={[questionIndex, setQuestionIndex]}>
      <ExamLayout
        title={title}
        totalQuestion={questionData.length}
        onSubmit={createSchoolExamSubmit}
      >
        <div className="grid gap-6">
          <HeroExam
            course={schoolExamData.exam_id.course}
            examTitle={schoolExamData.exam_id.title}
            expiryTimestamp={time}
          />
          <form onSubmit={createSchoolExamSubmit}>
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle>Pertanyaan {questionIndex}</CardTitle>
                <Button className="bg-gray-900" disabled>
                  {questionData[questionIndex - 1].question_type}
                </Button>
              </CardHeader>
              <CardContent className="grid gap-4">
                {htmlDecode(questionData[questionIndex - 1].question_text)}

                {questionData[questionIndex - 1].question_type === "Essay" ? (
                  <Textarea
                    placeholder="Jawaban"
                    value={data.answers[questionIndex - 1]?.answer_text}
                    onInput={
                      (e) => {
                        handleAnswer({
                          choice_id: null,
                          question_id: questionData[questionIndex - 1].id,
                          answer_text: e.target.value
                        })
                      }
                    }
                  />
                ) : questionData[questionIndex - 1].question_type === "True False" ? (
                  <RadioGroup
                    name={questionData[questionIndex - 1].id}
                    onValueChange={
                      (value) => {
                        handleAnswer({
                          choice_id: value,
                          question_id: questionData[questionIndex - 1].id,
                          answer_text: questionData[questionIndex - 1].choices.find(choice => choice.id == value).choice_text
                        })
                      }
                    }
                  >
                    {questionData[questionIndex - 1].choices.map((choice) => (
                      <div key={choice.id} className="flex items-center space-x-2">
                        <RadioGroupItem value={choice.id} id={choice.id} checked={data.answers[questionIndex - 1]?.choice_id == choice.id} />
                        <Label htmlFor={choice.id} className="cursor-pointer">{htmlDecode(choice.choice_text)}</Label>
                      </div>
                    ))}
                  </RadioGroup>
                ) : questionData[questionIndex - 1].question_type === "Pilihan Ganda" ? (
                  <RadioGroup
                    name={questionData[questionIndex - 1].id}
                    onValueChange={
                      (value) => {
                        handleAnswer({
                          choice_id: value,
                          question_id: questionData[questionIndex - 1].id,
                          answer_text: questionData[questionIndex - 1].choices.find(choice => choice.id == value).choice_text
                        })
                      }
                    }
                  >
                    {questionData[questionIndex - 1].choices.map((choice) => (
                      <div key={choice.id} className="flex items-center space-x-2">
                        <RadioGroupItem value={choice.id} id={choice.id} checked={data.answers[questionIndex - 1]?.choice_id == choice.id} />
                        <Label htmlFor={choice.id} className="cursor-pointer">{htmlDecode(choice.choice_text)}</Label>
                      </div>
                    ))}
                  </RadioGroup>
                ) : questionData[questionIndex - 1].question_type === "Pilihan Ganda Complex" ? (
                  questionData[questionIndex - 1].choices.map((choice, index) => (
                    <div className="flex items-center space-x-2" key={choice.id}>
                      <Checkbox
                        id={choice.id}
                        onCheckedChange={
                          () => {
                            handleAnswer({
                              choice_id: choice.id,
                              question_id: questionData[questionIndex - 1].id,
                              answer_text: choice.choice_text
                            }, choice.id)
                          }
                        }
                        checked={data.answers[questionIndex - 1]?.[index]?.choice_id == choice.id}
                      />
                      <div className="grid gap-1.5 leading-none">
                        <Label
                          htmlFor={choice.id}
                          className="cursor-pointer text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                          {htmlDecode(choice.choice_text)}
                        </Label>
                      </div>
                    </div>
                  ))
                ) : null}
              </CardContent>
            </Card>
          </form>
        </div>
      </ExamLayout>
    </QuestionIndexContext.Provider>
  )
}